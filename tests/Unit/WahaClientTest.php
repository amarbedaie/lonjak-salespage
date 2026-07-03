<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Waha\WahaClient;
use Waha\WahaException;

class WahaClientTest extends TestCase
{
    public function test_chat_id_normalizes_local_and_international_numbers(): void
    {
        $this->assertSame('60123456789@c.us', WahaClient::chatId('0123456789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('+60 12-345 6789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('60123456789'));
        $this->assertSame('60123456789@c.us', WahaClient::chatId('0060123456789'));
        $this->assertSame('', WahaClient::chatId('abc'));
    }

    public function test_send_text_posts_to_waha_and_returns_payload(): void
    {
        Http::fake(['*/api/sendText' => Http::response(['id' => 'true_123@c.us_ABC'], 201)]);

        $client = new WahaClient('https://waha.test', 'secret-key', 'amarbedaie');
        $result = $client->sendText('60123456789@c.us', 'Hai');

        $this->assertSame('true_123@c.us_ABC', $result['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://waha.test/api/sendText'
            && $request['session'] === 'amarbedaie'
            && $request['chatId'] === '60123456789@c.us'
            && $request['text'] === 'Hai'
            && $request->hasHeader('X-Api-Key', 'secret-key'));
    }

    public function test_send_text_throws_on_failure(): void
    {
        Http::fake(['*/api/sendText' => Http::response('unauthorized', 401)]);

        $this->expectException(WahaException::class);
        (new WahaClient('https://waha.test', 'bad', 'amarbedaie'))->sendText('60123456789@c.us', 'Hai');
    }

    public function test_send_text_wraps_connection_exception(): void
    {
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
        });

        $this->expectException(WahaException::class);
        (new WahaClient('https://waha.test', 'k', 'amarbedaie'))->sendText('60123456789@c.us', 'Hai');
    }
}
