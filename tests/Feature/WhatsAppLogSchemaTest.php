<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\WhatsAppLog;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppLogSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_row_is_unique_per_order_and_type(): void
    {
        $order = Order::factory()->create();
        WhatsAppLog::create(['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent']);

        $this->expectException(QueryException::class);
        WhatsAppLog::create(['order_id' => $order->id, 'type' => 'paid_customer', 'status' => 'sent']);
    }
}
