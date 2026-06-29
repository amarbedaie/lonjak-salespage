<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Webimpian\BayarcashSdk\Bayarcash;
use Webimpian\BayarcashSdk\Fpx;

/**
 * BayarCash (FPX / DuitNow) integration. Checksum is signed with the API SECRET.
 * The server callback (webhook) is authoritative; the browser return is best-effort.
 * (Named *Gateway to avoid a case-insensitive clash with the SDK's Bayarcash class.)
 */
class BayarcashGateway
{
    public static function enabled(): bool
    {
        return (bool) config('services.bayarcash.api_secret') && (bool) config('services.bayarcash.token');
    }

    private function client(): Bayarcash
    {
        $client = new Bayarcash(config('services.bayarcash.token'));
        if (config('services.bayarcash.sandbox')) {
            $client->useSandbox();
        }
        $client->setApiVersion(config('services.bayarcash.api_version', 'v3'));

        return $client;
    }

    public function createPayment(Order $order): RedirectResponse
    {
        $client = $this->client();
        $secret = config('services.bayarcash.api_secret');

        $data = [
            'payment_channel' => config('services.bayarcash.channel', 1),
            'portal_key' => config('services.bayarcash.portal_key'),
            'order_number' => 'LJK'.$order->id,
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'payer_name' => $order->customer,
            'payer_email' => $order->email ?: 'pelanggan@lonjak.my',
            'payer_telephone_number' => preg_replace('/\D/', '', $order->phone) ?: '0123456789',
            'return_url' => route('payment.return'),
            'callback_url' => route('payment.callback'),
        ];
        $data['checksum'] = $client->createPaymentIntentChecksumValue($secret, $data);

        $intent = $client->createPaymentIntent($data);

        $order->update(['payment_ref' => $intent->id ?? null]);

        return redirect()->away($intent->url);
    }

    /** Verify + apply a callback (return or webhook). Returns the order if paid. */
    public function apply(array $callback, bool $isWebhook): ?Order
    {
        $client = $this->client();
        $secret = config('services.bayarcash.api_secret');

        $verified = $isWebhook
            ? $client->verifyTransactionCallbackData($callback, $secret)
            : $client->verifyReturnUrlCallbackData($callback, $secret);

        if (! $verified) {
            return null;
        }

        $id = (int) preg_replace('/\D/', '', $callback['order_number'] ?? '');
        $order = Order::find($id);
        if (! $order) {
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
}
