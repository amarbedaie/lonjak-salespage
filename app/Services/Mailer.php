<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Transactional emails. Sends via the configured mailer (ZeptoMail HTTP API in
 * production, 'log' locally). Failures are reported but never break the flow.
 */
class Mailer
{
    public static function welcome(User $user): void
    {
        self::send($user->email, $user->business_name ?? 'Usahawan', 'Selamat datang ke Lonjak 🎉', 'emails.welcome', ['user' => $user]);
    }

    public static function orderReceived(Order $order): void
    {
        $merchant = $order->user;
        if ($merchant?->email) {
            self::send($merchant->email, $merchant->business_name, "Order baru daripada {$order->customer}", 'emails.order-merchant', ['order' => $order, 'merchant' => $merchant]);
        }
        if ($order->email) {
            self::send($order->email, $order->customer, 'Terima kasih atas tempahan anda', 'emails.order-customer', ['order' => $order]);
        }
    }

    public static function orderPaid(Order $order): void
    {
        $merchant = $order->user;
        if ($merchant?->email) {
            self::send($merchant->email, $merchant->business_name, "Bayaran diterima — order {$order->customer}", 'emails.order-paid', ['order' => $order]);
        }
    }

    private static function send(string $to, ?string $name, string $subject, string $view, array $data): void
    {
        try {
            Mail::send($view, $data, fn ($m) => $m->to($to, $name ?: $to)->subject($subject));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
