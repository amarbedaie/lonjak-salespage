<?php

namespace Tests\Feature;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderPaidWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('whatsapp.enabled', true);
        config()->set('whatsapp.admin_number', '0198887777');
        config()->set('waha.url', 'https://waha.test');
        config()->set('waha.api_key', 'k');
        config()->set('waha.session', 'amarbedaie');
        config()->set('waha.default_cc', '60');
    }

    public function test_paid_order_sends_customer_and_merchant_whatsapp(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.X'], 201)]);
        $merchant = User::factory()->create(['notify_whatsapp' => '0191112222']);
        $order = Order::factory()->for($merchant, 'user')->create(['phone' => '0123456789']);

        event(new OrderPaid($order));

        Http::assertSentCount(2);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent', 'to' => '60123456789@c.us']);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_merchant', 'status' => 'sent', 'to' => '60191112222@c.us']);
    }

    public function test_it_is_idempotent_across_repeated_events(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.X'], 201)]);
        $order = Order::factory()->create(['phone' => '0123456789']);

        event(new OrderPaid($order));
        event(new OrderPaid($order));

        Http::assertSentCount(2); // customer + merchant once, not four
    }

    public function test_missing_customer_phone_is_logged_failed(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'x'], 201)]);
        config()->set('whatsapp.admin_number', null);
        $merchant = User::factory()->create(['notify_whatsapp' => null]);
        $order = Order::factory()->for($merchant, 'user')->create(['phone' => '']);

        event(new OrderPaid($order));

        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'failed', 'error' => 'no_phone']);
    }

    public function test_disabled_flag_sends_nothing(): void
    {
        config()->set('whatsapp.enabled', false);
        Http::fake();
        $order = Order::factory()->create(['phone' => '0123456789']);

        event(new OrderPaid($order));

        Http::assertNothingSent();
    }

    public function test_customer_send_failure_does_not_block_merchant(): void
    {
        Http::fakeSequence()
            ->push('waha error', 500)          // customer send fails
            ->push(['id' => 'wamid.M'], 201);  // merchant send succeeds

        $order = Order::factory()->create(['phone' => '0123456789']);

        try {
            event(new OrderPaid($order));
            $this->fail('Expected WahaException to trigger a queue retry.');
        } catch (\Waha\WahaException $e) {
            // expected — handle() attempts both recipients, then rethrows for retry
        }

        Http::assertSentCount(2); // both recipients attempted despite the customer failure
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'failed']);
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'paid_merchant', 'status' => 'sent']);
    }

    public function test_flag_off_does_not_queue_the_listener(): void
    {
        config()->set('whatsapp.enabled', false);
        \Illuminate\Support\Facades\Queue::fake();

        $order = Order::factory()->create(['phone' => '0123456789']);
        event(new OrderPaid($order));

        \Illuminate\Support\Facades\Queue::assertNothingPushed();
    }
}
