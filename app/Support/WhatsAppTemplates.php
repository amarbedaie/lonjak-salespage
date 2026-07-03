<?php

namespace App\Support;

use App\Models\Order;

class WhatsAppTemplates
{
    /** Best available store/brand name for sign-off. */
    public static function store(Order $order): string
    {
        return $order->salespage?->title
            ?: ($order->user?->business_name ?: ($order->user?->name ?: 'BeDaie'));
    }

    public static function paidCustomer(Order $order): string
    {
        $store = self::store($order);
        $total = number_format((float) $order->total, 2);

        return "Assalamualaikum {$order->customer} 🌙\n\n"
            ."Terima kasih! Pesanan #{$order->id} anda telah BERJAYA dibayar ✅\n\n"
            ."🛍️ {$order->product_name} × {$order->qty}\n"
            ."💰 RM{$total}\n\n"
            ."Pesanan anda sedang diproses. Butiran penghantaran akan menyusul, insyaAllah 🤍\n— {$store}";
    }

    public static function paidMerchant(Order $order): string
    {
        $total = number_format((float) $order->total, 2);

        return "🔔 ORDER BARU DIBAYAR ✅ #{$order->id}\n\n"
            ."👤 {$order->customer}\n"
            ."📞 {$order->phone}\n"
            ."🛍️ {$order->product_name} × {$order->qty}\n"
            ."💰 RM{$total}\n"
            ."📍 {$order->state}";
    }

    public static function shipped(Order $order): string
    {
        $store = self::store($order);

        return "📦 Pesanan #{$order->id} anda telah dihantar!\n\n"
            ."Kurier: {$order->courier}\n"
            ."No. Tracking: {$order->awb}\n"
            ."Jejak: {$order->tracking_url}\n\n"
            ."Terima kasih kerana membeli 🤍\n— {$store}";
    }
}
