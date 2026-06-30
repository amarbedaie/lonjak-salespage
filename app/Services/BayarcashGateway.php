<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Webimpian\BayarcashSdk\Bayarcash;
use Webimpian\BayarcashSdk\Fpx;

/**
 * Per-merchant BayarCash (FPX / DuitNow) gateway. Each merchant stores their own
 * credentials, so a salespage checkout charges the *salespage owner's* account.
 * Checksum is signed with the API SECRET; the server webhook is authoritative.
 */
class BayarcashGateway
{
    public function __construct(
        private string $pat,
        private string $portalKey,
        private string $apiSecret,
        private bool $sandbox = false,
    ) {}

    public static function forMerchant(User $merchant): ?self
    {
        if (! $merchant->hasBayarcash()) {
            return null;
        }

        return new self(
            $merchant->bayarcash_pat,
            $merchant->bayarcash_portal_key,
            $merchant->bayarcash_api_secret,
            (bool) $merchant->bayarcash_sandbox,
        );
    }

    private function client(): Bayarcash
    {
        $client = new Bayarcash($this->pat);
        if ($this->sandbox) {
            $client->useSandbox();
        }
        $client->setApiVersion('v3');

        return $client;
    }

    public function createPayment(Order $order): RedirectResponse
    {
        $client = $this->client();

        $data = [
            'payment_channel' => 1, // FPX
            'portal_key' => $this->portalKey,
            'order_number' => 'LJK'.$order->id,
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'payer_name' => $order->customer,
            'payer_email' => $order->email ?: 'pelanggan@lonjak.my',
            'payer_telephone_number' => preg_replace('/\D/', '', $order->phone) ?: '0123456789',
            'return_url' => route('payment.return'),
            'callback_url' => route('payment.callback'),
        ];
        $data['checksum'] = $client->createPaymentIntentChecksumValue($this->apiSecret, $data);

        $intent = $client->createPaymentIntent($data);
        $order->update(['payment_ref' => $intent->id ?? null]);

        return redirect()->away($intent->url);
    }

    /** Verify a callback against the order owner's secret; apply status. Returns the order if paid. */
    public static function applyCallback(array $callback, bool $isWebhook): ?Order
    {
        $id = (int) preg_replace('/\D/', '', $callback['order_number'] ?? '');
        $order = Order::with('user')->find($id);
        if (! $order || ! $order->user) {
            return null;
        }
        $gateway = self::forMerchant($order->user);
        if (! $gateway) {
            return null;
        }

        $client = $gateway->client();
        $verified = $isWebhook
            ? $client->verifyTransactionCallbackData($callback, $gateway->apiSecret)
            : $client->verifyReturnUrlCallbackData($callback, $gateway->apiSecret);
        if (! $verified) {
            return null;
        }

        $status = (int) ($callback['status'] ?? 0);
        if ($status === Fpx::STATUS_SUCCESS) {
            $order->update(['payment_status' => 'dibayar', 'status' => 'diproses']);

            return $order;
        }
        if ($status === Fpx::STATUS_FAILED) {
            $order->update(['payment_status' => 'gagal']);
        }

        return null;
    }

    /** Validate raw credentials by hitting the portals endpoint. */
    public static function validateCredentials(string $pat, bool $sandbox): bool
    {
        try {
            $client = new Bayarcash($pat);
            if ($sandbox) {
                $client->useSandbox();
            }
            $client->setApiVersion('v3');
            $client->getPortals();

            return true;
        } catch (\Throwable $e) {
            // The SDK throws a TypeError while *parsing* a valid response (null websiteUrl);
            // that still proves the token authenticated. Only a 401/auth message is a real failure.
            return ! str_contains(strtolower($e->getMessage()), 'unauthenticated');
        }
    }
}
