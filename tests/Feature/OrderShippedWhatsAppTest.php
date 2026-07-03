<?php

namespace Tests\Feature;

use App\Events\OrderShipped;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrderShippedWhatsAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('whatsapp.enabled', true);
        config()->set('waha.url', 'https://waha.test');
        config()->set('waha.api_key', 'k');
        config()->set('waha.session', 'amarbedaie');
        config()->set('waha.default_cc', '60');
    }

    public function test_shipped_order_sends_tracking_to_customer(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'wamid.SHIP'], 201)]);
        $order = Order::factory()->create([
            'phone' => '0123456789',
            'courier' => 'J&T',
            'awb' => 'JT123456',
            'tracking_url' => 'https://track.test/JT123456',
        ]);

        event(new OrderShipped($order));

        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_contains($r['text'], 'JT123456') && $r['chatId'] === '60123456789@c.us');
        $this->assertDatabaseHas('whatsapp_logs', ['order_id' => $order->id, 'type' => 'shipped', 'status' => 'sent']);
    }
}
