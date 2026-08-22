<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Caller for the universal BeDaie provisioning contract.
 *
 * POST <system>/api/provision
 *   Authorization: Bearer <PROVISIONING_API_KEY>
 *   X-Signature:   HMAC-SHA256(raw body, PROVISIONING_SIGNING_SECRET) as lowercase hex
 *   body: {idempotency_key, order_ref?, order_id?, customer:{email, name?, phone?}}
 *   → {external_user_id, login_url, username, magic_link}
 *
 * The receiving end is idempotent: replaying the same idempotency_key returns
 * the stored response instead of granting twice.
 */
class ProvisioningClient
{
    /** @return array{external_user_id?:string,login_url?:string,username?:string,magic_link?:string} */
    public function provision(string $system, array $payload): array
    {
        $url = (string) config("provisioning.systems.{$system}.url");
        $key = (string) config('provisioning.key');
        $secret = (string) config('provisioning.secret');

        if ($url === '') {
            throw new ProvisioningException("Unknown provisioning system '{$system}'.");
        }
        if ($key === '' || $secret === '') {
            throw new ProvisioningException('PROVISIONING_API_KEY / PROVISIONING_SIGNING_SECRET not configured.');
        }

        // Sign the EXACT bytes we send — the receiver HMACs the raw request body,
        // so the payload must be encoded once and reused, never re-encoded.
        $raw = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        try {
            $response = Http::withToken($key)
                ->withHeaders([
                    'X-Signature' => hash_hmac('sha256', $raw, $secret),
                    'Content-Type' => 'application/json',
                ])
                ->timeout((int) config('provisioning.timeout', 20))
                ->acceptJson()
                ->withBody($raw, 'application/json')
                ->post($url);
        } catch (ConnectionException $e) {
            throw new ProvisioningException("Provision {$system} connection failed: ".$e->getMessage(), retryable: true, previous: $e);
        }

        if ($response->failed()) {
            // 5xx / 429 are transient — worth a queue retry. 4xx means the request
            // itself is wrong (bad key, bad signature, invalid email); retrying can't fix it.
            $retryable = $response->serverError() || $response->status() === 429;

            throw new ProvisioningException(
                "Provision {$system} failed ({$response->status()}): ".substr($response->body(), 0, 300),
                retryable: $retryable,
            );
        }

        return $response->json() ?? [];
    }
}
