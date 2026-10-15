<?php

namespace Tests\Unit;

use App\Services\AI\ClaudeProvider;
use App\Services\SalespageGenerator;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class ClaudeProviderTest extends TestCase
{
    private function anthropicReply(string $text): array
    {
        return ['content' => [['type' => 'text', 'text' => $text]]];
    }

    public function test_chat_sends_anthropic_headers_and_lifts_system_prompt(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->anthropicReply('hi'), 200)]);

        $provider = new ClaudeProvider('https://api.anthropic.com/v1', 'sk-ant-test', 'claude-sonnet-5-5');
        $out = $provider->chat([
            ['role' => 'system', 'content' => 'You are a bot.'],
            ['role' => 'user', 'content' => 'Hello'],
        ]);

        $this->assertSame('hi', $out);
        Http::assertSent(function ($request) {
            $body = $request->data();

            return $request->url() === 'https://api.anthropic.com/v1/messages'
                && $request->hasHeader('x-api-key', 'sk-ant-test')
                && $request->hasHeader('anthropic-version', '2023-06-01')
                && ($body['system'] ?? null) === 'You are a bot.'       // system lifted out of messages
                && count($body['messages']) === 1                       // only the user turn remains
                && $body['messages'][0]['role'] === 'user'
                && ! array_key_exists('temperature', $body);            // never send temperature on 5.x
        });
    }

    public function test_json_option_appends_a_json_only_system_line(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->anthropicReply('{}'), 200)]);

        (new ClaudeProvider('https://api.anthropic.com/v1', 'sk-ant-test', 'claude-haiku-5-5'))
            ->chat([['role' => 'user', 'content' => 'x']], ['json' => true]);

        Http::assertSent(fn ($request) => str_contains($request->data()['system'] ?? '', 'JSON'));
    }

    public function test_per_call_model_override_only_honours_claude_ids(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response($this->anthropicReply('x'), 200)]);
        $provider = new ClaudeProvider('https://api.anthropic.com/v1', 'sk-ant-test', 'claude-sonnet-5-5');

        $provider->chat([['role' => 'user', 'content' => 'x']], ['model' => 'claude-haiku-5-5']);
        Http::assertSent(fn ($r) => $r->data()['model'] === 'claude-haiku-5-5');

        $provider->chat([['role' => 'user', 'content' => 'x']], ['model' => 'gpt-4o-mini']);
        Http::assertSent(fn ($r) => $r->data()['model'] === 'claude-sonnet-5-5'); // bad id ignored
    }

    public function test_missing_key_throws(): void
    {
        $this->expectException(RuntimeException::class);
        (new ClaudeProvider('https://api.anthropic.com/v1', null, 'claude-sonnet-5-5'))
            ->chat([['role' => 'user', 'content' => 'x']]);
    }

    public function test_generator_routes_copy_to_claude_when_configured(): void
    {
        config(['ai.provider' => 'claude', 'ai.claude.api_key' => 'sk-ant-test', 'ai.claude.model' => 'claude-sonnet-5-5']);
        Http::fake([
            'api.anthropic.com/*' => Http::response($this->anthropicReply(
                json_encode(['blocks' => [['type' => 'hero', 'headline' => 'Tajuk']]])
            ), 200),
        ]);

        $result = (new SalespageGenerator())->generate(['name' => 'Buku X', 'price' => 49]);

        $this->assertSame('claude', $result['source']);
        $this->assertSame('hero', $result['page']['blocks'][0]['type']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.anthropic.com'));
    }
}
