<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\WhatsAppLog;
use App\Support\WhatsAppTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;
use Waha\WahaClient;
use Waha\WahaException;

class SendPaidWhatsAppNotifications implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(private WahaClient $waha) {}

    public function handle(OrderPaid $event): void
    {
        if (! config('whatsapp.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('user', 'salespage');

        $this->send($order, 'paid_customer', $order->phone, WhatsAppTemplates::paidCustomer($order));

        $merchantNumber = $order->user?->notify_whatsapp ?: config('whatsapp.admin_number');
        $this->send($order, 'paid_merchant', $merchantNumber, WhatsAppTemplates::paidMerchant($order));
    }

    /** Idempotent single send: one row per (order, type); never re-sends an already-sent message. */
    private function send(Order $order, string $type, ?string $phone, string $text): void
    {
        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => $type]);
        if ($log->status === 'sent') {
            return;
        }

        $chatId = $phone ? WahaClient::chatId($phone, $this->waha->defaultCc()) : '';
        if ($chatId === '') {
            $log->fill(['to' => $phone, 'status' => 'failed', 'error' => 'no_phone'])->save();

            return;
        }

        $log->fill(['to' => $chatId, 'session' => config('waha.session'), 'status' => 'queued'])->save();

        try {
            $res = $this->waha->sendText($chatId, $text);
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
            throw $e; // allow the queue to retry
        }
    }
}
