<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\Order;
use App\Models\ProvisioningCall;
use App\Models\WhatsAppLog;
use App\Services\ProvisioningClient;
use App\Services\ProvisioningException;
use App\Support\WhatsAppTemplates;
use Illuminate\Contracts\Queue\ShouldQueue;
use Waha\WahaClient;
use Waha\WahaException;

/**
 * A paid order on a salespage tied to a BeDaie system grants the customer
 * access there, then WhatsApps them the magic login link.
 */
class ProvisionPurchasedSystemAccess implements ShouldQueue
{
    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(private ProvisioningClient $client, private WahaClient $waha) {}

    public function shouldQueue(OrderPaid $event): bool
    {
        return (bool) config('provisioning.enabled') && self::systemFor($event->order) !== null;
    }

    public function handle(OrderPaid $event): void
    {
        if (! config('provisioning.enabled')) {
            return;
        }

        $order = $event->order->loadMissing('salespage');
        $system = self::systemFor($order);
        if ($system === null) {
            return;
        }

        $call = ProvisioningCall::firstOrNew(['order_id' => $order->id, 'system' => $system]);
        if ($call->status === 'done') {
            $this->sendMagicLink($order, $call); // link may still be undelivered

            return;
        }

        $call->fill([
            'idempotency_key' => $call->idempotency_key ?: "mendap-order-{$order->id}-{$system}",
            'status' => 'pending',
            'attempts' => $call->attempts + 1,
        ])->save();

        try {
            $result = $this->client->provision($system, [
                'idempotency_key' => $call->idempotency_key,
                'order_ref' => 'MENDAP-'.$order->id,
                'order_id' => $order->id,
                'customer' => array_filter([
                    'email' => $order->email,
                    'name' => $order->customer,
                    'phone' => $order->phone,
                ]),
            ]);
        } catch (ProvisioningException $e) {
            $call->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);

            if ($e->retryable) {
                throw $e; // queue retries; the idempotency_key makes a replay safe
            }

            return;
        }

        $call->update([
            'status' => 'done',
            'external_user_id' => isset($result['external_user_id']) ? (string) $result['external_user_id'] : null,
            'magic_link' => $result['magic_link'] ?? null,
            'login_url' => $result['login_url'] ?? null,
            'response' => $result,
            'error' => null,
        ]);

        $this->sendMagicLink($order, $call);
    }

    /** The system a paid order should provision, or null when the page sells no system access. */
    public static function systemFor(Order $order): ?string
    {
        $system = $order->salespage?->provision_system;

        return $system && config("provisioning.systems.{$system}") ? $system : null;
    }

    /**
     * WhatsApp the access link to the customer. Idempotent via whatsapp_logs
     * (unique per order+type) and silent when WhatsApp notifications are off —
     * a delivery failure must never undo a successful grant.
     */
    private function sendMagicLink(Order $order, ProvisioningCall $call): void
    {
        if (! config('whatsapp.enabled') || ! $call->magic_link) {
            return;
        }

        $log = WhatsAppLog::firstOrNew(['order_id' => $order->id, 'type' => 'provision_access']);
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
            $res = $this->waha->sendText($chatId, WhatsAppTemplates::provisionAccess($order, $call));
            $log->update(['status' => 'sent', 'waha_message_id' => $res['id'] ?? null, 'error' => null]);
        } catch (WahaException $e) {
            $log->update(['status' => 'failed', 'error' => substr($e->getMessage(), 0, 500)]);
        }
    }
}
