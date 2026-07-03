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

    public function shouldQueue(OrderPaid $event): bool
    {
        return (bool) config('whatsapp.enabled');
    }

    public function handle(OrderPaid $event): void
    {
        if (! config('whatsapp.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('user', 'salespage');

        $recipients = [
            ['paid_customer', $order->phone, WhatsAppTemplates::paidCustomer($order)],
            ['paid_merchant', $order->user?->notify_whatsapp ?: config('whatsapp.admin_number'), WhatsAppTemplates::paidMerchant($order)],
        ];

        // Attempt BOTH recipients independently — one recipient's WAHA failure
        // must never prevent the other from being tried.
        $retryable = false;
        foreach ($recipients as [$type, $phone, $text]) {
            if (! $this->send($order, $type, $phone, $text)) {
                $retryable = true;
            }
        }

        // If any send hit a transient WAHA error, throw now so the queue retries;
        // idempotency skips the ones already marked 'sent' on the next attempt.
        if ($retryable) {
            throw new WahaException('One or more WhatsApp notifications failed for order '.$order->id.'; queued for retry.');
        }
    }

    /**
     * Idempotent single send. Returns false only on a retryable WAHA error;
     * true when sent, already sent, or permanently skipped (no phone).
     */
    private function send(Order $order, string $type, ?string $phone, string $text): bool
    {
        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => $type]);
        if ($log->status === 'sent') {
            return true;
        }

        $chatId = $phone ? WahaClient::chatId($phone, $this->waha->defaultCc()) : '';
        if ($chatId === '') {
            $log->fill(['to' => $phone, 'status' => 'failed', 'error' => 'no_phone'])->save();

            return true; // missing phone is permanent — not retryable
        }

        $log->fill(['to' => $chatId, 'session' => config('waha.session'), 'status' => 'queued'])->save();

        try {
            $res = $this->waha->sendText($chatId, $text);
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);

            return true;
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);

            return false; // retryable
        }
    }
}
