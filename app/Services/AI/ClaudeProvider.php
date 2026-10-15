<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Anthropic Claude provider — official Messages API (https://docs.claude.com).
 *
 * Standalone, dependency-free (raw HTTP via Laravel's client). Turns an
 * OpenAI-style [{role,content}] message array into Anthropic's shape (the
 * system prompt is a TOP-LEVEL field, not a message) and returns the
 * assistant's text. Powered by the Max/Team plan's monthly API credits:
 * set ANTHROPIC_API_KEY to a key from the linked Console organization.
 *
 * Call it directly, bind it behind your own provider interface, or register it
 * in the container (see the skill's SKILL.md).
 */
class ClaudeProvider
{
    public function __construct(
        protected string $baseUrl = 'https://api.anthropic.com/v1',
        protected ?string $apiKey = null,
        protected string $model = 'claude-haiku-5-5',
        protected int $timeout = 60,
        protected string $version = '2023-06-01',
        protected int $defaultMaxTokens = 1500,
    ) {}

    /**
     * @param  array<int, array{role:string, content:string}>  $messages
     * @param  array<string, mixed>  $options  Supports: max_tokens (int), json (bool),
     *                                          model (string, a claude-* id to override
     *                                          the default for THIS call only).
     */
    public function chat(array $messages, array $options = []): string
    {
        if (blank($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set.');
        }

        // Per-call model override: use a harder/cheaper model for one feature
        // without changing the project default. Only honour real Claude ids so
        // a leftover OpenAI model name (e.g. gpt-4o-mini) falls back safely.
        $model = $this->model;
        $requested = $options['model'] ?? null;
        if (is_string($requested) && str_starts_with($requested, 'claude-')) {
            $model = $requested;
        }

        // Anthropic keeps the system prompt out of the messages array.
        $system = [];
        $chat = [];
        foreach ($messages as $m) {
            $role = $m['role'] ?? 'user';
            $content = (string) ($m['content'] ?? '');

            if ($role === 'system') {
                if ($content !== '') {
                    $system[] = $content;
                }

                continue;
            }

            // Anthropic only accepts user/assistant turns here.
            $chat[] = [
                'role' => $role === 'assistant' ? 'assistant' : 'user',
                'content' => $content,
            ];
        }

        // The conversation must start with a user turn; drop any leading
        // assistant turns a sliced history window may begin with.
        while (! empty($chat) && $chat[0]['role'] === 'assistant') {
            array_shift($chat);
        }

        // Reinforce JSON-only output when the caller asked for it.
        if (($options['json'] ?? false)) {
            $system[] = 'Reply with ONE valid JSON object only. No prose, no comments, no code fences (```).';
        }

        $payload = [
            'model' => $model,
            'max_tokens' => (int) ($options['max_tokens'] ?? $this->defaultMaxTokens),
            'messages' => $chat,
        ];

        if ($system !== []) {
            $payload['system'] = implode("\n\n", $system);
        }

        // NOTE: do NOT send `temperature` — current 5.x models reject non-default
        // sampling values (400). Omit it and the request is model-agnostic.

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->version,
        ])
            ->timeout($this->timeout)
            ->acceptJson()
            ->post(rtrim($this->baseUrl, '/').'/messages', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Claude request failed: '.$response->status().' '.$response->body());
        }

        // Anthropic returns `content` as an array of blocks; concatenate text
        // (thinking blocks are ignored).
        $text = '';
        foreach ((array) data_get($response->json(), 'content', []) as $block) {
            if (($block['type'] ?? null) === 'text') {
                $text .= (string) ($block['text'] ?? '');
            }
        }

        return $text;
    }

    public function name(): string
    {
        return 'claude:'.$this->model;
    }
}
