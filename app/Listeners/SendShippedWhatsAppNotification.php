<?php

namespace App\Listeners;

use App\Events\OrderShipped;
use App\Models\WhatsAppLog;
use App\Support\WhatsAppTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;
use Waha\WahaClient;
use Waha\WahaException;

class SendShippedWhatsAppNotification implements ShouldQueue
{
    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(private WahaClient $waha) {}

    public function shouldQueue(OrderShipped $event): bool
    {
        return (bool) config('whatsapp.enabled');
    }

    public function handle(OrderShipped $event): void
    {
        if (! config('whatsapp.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('user', 'salespage');

        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => 'shipped']);
        if ($log->status === 'sent') {
            return;
        }

        $chatId = $order->phone ? WahaClient::chatId($order->phone, $this->waha->defaultCc()) : '';
        if ($chatId === '') {
            $log->fill(['to' => $order->phone, 'status' => 'failed', 'error' => 'no_phone'])->save();

            return;
        }

        $log->fill(['to' => $chatId, 'session' => config('waha.session'), 'status' => 'queued'])->save();

        try {
            $res = $this->waha->sendText($chatId, WhatsAppTemplates::shipped($order));
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
            throw $e;
        }
    }
}
