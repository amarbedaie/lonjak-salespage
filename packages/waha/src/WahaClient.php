<?php

namespace Waha;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class WahaClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private string $defaultSession = 'default',
        private string $defaultCc = '60',
        private int $timeout = 15,
    ) {}

    /** Normalize a phone number to a WAHA chat id ("60123456789@c.us"). Returns '' when there are no digits. */
    public static function chatId(string $phone, string $defaultCc = '60'): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = $defaultCc.substr($digits, 1);
        }

        return $digits.'@c.us';
    }

    /** Send a text message. Throws WahaException on a non-2xx response. */
    public function sendText(string $chatId, string $text, ?string $session = null): array
    {
        try {
            $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
                ->timeout($this->timeout)
                ->acceptJson()
                ->post(rtrim($this->baseUrl, '/').'/api/sendText', [
                    'session' => $session ?? $this->defaultSession,
                    'chatId' => $chatId,
                    'text' => $text,
                ]);
        } catch (ConnectionException $e) {
            throw new WahaException('WAHA sendText connection failed: '.$e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new WahaException("WAHA sendText failed ({$response->status()}): ".$response->body());
        }

        return $response->json() ?? [];
    }

    public function defaultCc(): string
    {
        return $this->defaultCc;
    }
}
