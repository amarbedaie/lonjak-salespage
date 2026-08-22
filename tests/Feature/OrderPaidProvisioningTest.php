<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Listeners\ProvisionPurchasedSystemAccess;
use App\Models\Order;
use App\Models\ProvisioningCall;
use App\Models\Salespage;
use App\Services\ProvisioningException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderPaidProvisioningTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-api-key';

    private const SECRET = 'test-signing-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('provisioning.enabled', true);
        config()->set('provisioning.key', self::KEY);
        config()->set('provisioning.secret', self::SECRET);
        config()->set('provisioning.systems.statushadis', [
            'name' => 'Status Hadis',
            'url' => 'https://statushadis.test/api/provision',
        ]);
        // WhatsApp delivery is exercised only in the test that turns it on.
        config()->set('whatsapp.enabled', false);
        config()->set('waha.url', 'https://waha.test');
        config()->set('waha.api_key', 'k');
        config()->set('waha.session', 'amarbedaie');
        config()->set('waha.default_cc', '60');
    }

    private function order(?string $system = 'statushadis'): Order
    {
        return Order::factory()
            ->for(Salespage::factory()->create(['provision_system' => $system]), 'salespage')
            ->create(['email' => 'ali@example.com', 'customer' => 'Ali', 'phone' => '0123456789']);
    }

    private function fakeSuccess(): void
    {
        Http::fake(['https://statushadis.test/api/provision' => Http::response([
            'external_user_id' => '42',
            'login_url' => 'https://statushadis.com/masuk',
            'username' => 'ali@example.com',
            'magic_link' => 'https://statushadis.com/provision/magic/42?signature=abc',
        ], 200)]);
    }

    public function test_paid_order_provisions_the_linked_system(): void
    {
        $this->fakeSuccess();
        $order = $this->order();

        event(new OrderPaid($order));

        Http::assertSentCount(1);
        $this->assertDatabaseHas('provisioning_calls', [
            'order_id' => $order->id,
            'system' => 'statushadis',
            'status' => 'done',
            'external_user_id' => '42',
            'idempotency_key' => "mendap-order-{$order->id}-statushadis",
        ]);
    }

    public function test_request_carries_bearer_token_and_hmac_over_the_raw_body(): void
    {
        $this->fakeSuccess();
        $order = $this->order();

        event(new OrderPaid($order));

        Http::assertSent(function ($request) {
            $raw = $request->body();

            return $request->hasHeader('Authorization', 'Bearer '.self::KEY)
                && $request->hasHeader('X-Signature', hash_hmac('sha256', $raw, self::SECRET))
                && json_decode($raw, true)['customer']['email'] === 'ali@example.com';
        });
    }

    public function test_repeated_events_provision_only_once(): void
    {
        $this->fakeSuccess();
        $order = $this->order();

        event(new OrderPaid($order));
        event(new OrderPaid($order));

        Http::assertSentCount(1);
        $this->assertSame(1, ProvisioningCall::where('order_id', $order->id)->count());
    }

    public function test_salespage_without_a_system_provisions_nothing(): void
    {
        Http::fake();
        $order = $this->order(null);

        event(new OrderPaid($order));

        Http::assertNothingSent();
        $this->assertDatabaseCount('provisioning_calls', 0);
    }

    public function test_disabled_flag_does_not_queue_the_listener(): void
    {
        config()->set('provisioning.enabled', false);
        Queue::fake();

        event(new OrderPaid($this->order()));

        Queue::assertNothingPushed();
    }

    public function test_client_error_is_recorded_without_retrying(): void
    {
        Http::fake(['https://statushadis.test/api/provision' => Http::response('Invalid signature', 401)]);
        $order = $this->order();

        event(new OrderPaid($order)); // must not throw — a 401 can't be fixed by retrying

        $this->assertDatabaseHas('provisioning_calls', ['order_id' => $order->id, 'status' => 'failed', 'attempts' => 1]);
    }

    public function test_server_error_throws_so_the_queue_retries(): void
    {
        Http::fake(['https://statushadis.test/api/provision' => Http::response('boom', 503)]);
        $order = $this->order();

        $this->expectException(ProvisioningException::class);
        event(new OrderPaid($order));
    }

    public function test_retry_after_a_failure_reuses_the_same_idempotency_key(): void
    {
        Http::fakeSequence()
            ->push('boom', 503)
            ->push(['external_user_id' => '42', 'magic_link' => 'https://statushadis.com/m'], 200);
        $order = $this->order();

        try {
            event(new OrderPaid($order));
        } catch (ProvisioningException $e) {
            // first attempt fails; queue would retry
        }
        event(new OrderPaid($order));

        $call = ProvisioningCall::where('order_id', $order->id)->sole();
        $this->assertSame('done', $call->status);
        $this->assertSame(2, $call->attempts);
        $this->assertSame("mendap-order-{$order->id}-statushadis", $call->idempotency_key);
    }

    public function test_magic_link_is_whatsapped_to_the_customer_when_enabled(): void
    {
        config()->set('whatsapp.enabled', true);
        Http::fake([
            'https://statushadis.test/api/provision' => Http::response([
                'external_user_id' => '42',
                'login_url' => 'https://statushadis.com/masuk',
                'magic_link' => 'https://statushadis.com/provision/magic/42?signature=abc',
            ], 200),
            '*/api/sendText' => Http::response(['id' => 'wamid.X'], 201),
        ]);
        $order = $this->order();

        event(new OrderPaid($order));

        $this->assertDatabaseHas('whatsapp_logs', [
            'order_id' => $order->id,
            'type' => 'provision_access',
            'status' => 'sent',
            'to' => '60123456789@c.us',
        ]);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'sendText')
            && str_contains($request['text'], 'https://statushadis.com/provision/magic/42?signature=abc'));
    }

    public function test_whatsapp_failure_does_not_undo_the_grant(): void
    {
        config()->set('whatsapp.enabled', true);
        Http::fake([
            'https://statushadis.test/api/provision' => Http::response(['external_user_id' => '42', 'magic_link' => 'https://x.test/m'], 200),
            '*/api/sendText' => Http::response('waha down', 500),
        ]);
        $order = $this->order();

        // Invoked directly: in production each ShouldQueue listener is its own queued
        // job, so the paid-order WhatsApp listener's retry can't mask this one.
        app(ProvisionPurchasedSystemAccess::class)->handle(new OrderPaid($order)); // must not throw

        $this->assertDatabaseHas('provisioning_calls', ['order_id' => $order->id, 'status' => 'done']);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'provision_access', 'status' => 'failed']);
    }
}
