<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Support\WhatsAppTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppTemplatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_customer_message_contains_order_details(): void
    {
        $order = Order::factory()->create(['customer' => 'Ali', 'product_name' => 'Buku Fatihah', 'qty' => 2, 'total' => 80]);

        $msg = WhatsAppTemplates::paidCustomer($order);

        $this->assertStringContainsString('Ali', $msg);
        $this->assertStringContainsString("#{$order->id}", $msg);
        $this->assertStringContainsString('Buku Fatihah × 2', $msg);
        $this->assertStringContainsString('RM80.00', $msg);
    }

    public function test_paid_merchant_message_contains_phone_and_total(): void
    {
        $order = Order::factory()->create(['phone' => '0191112222', 'total' => 49]);

        $msg = WhatsAppTemplates::paidMerchant($order);

        $this->assertStringContainsString('0191112222', $msg);
        $this->assertStringContainsString('RM49.00', $msg);
    }
}
