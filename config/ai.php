<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI provider for salespage COPY generation (text / LLM only)
    |--------------------------------------------------------------------------
    |
    | Which LLM powers the text copy in App\Services\SalespageGenerator:
    |   'claude'     — Anthropic Messages API, billed to Amar's Max-plan
    |                  monthly API credits ($200/mo, Amar's Individual Org).
    |   'openrouter' — the original OpenRouter path (kept as fallback).
    |
    | Claude is only actually used when the provider is 'claude' AND an
    | ANTHROPIC_API_KEY is present; otherwise the generator transparently
    | falls back to OpenRouter (and then to the local mock). This keeps every
    | environment working whether or not a Claude key is configured.
    |
    | NOTE: this switch covers TEXT generation only. Image generation and
    | image/vision analysis stay on their own provider (Google via OpenRouter)
    | regardless of this setting — see config/services.php 'openrouter'.
    |
    */

    'provider' => env('AI_PROVIDER', 'claude'),

    'claude' => [
        'base_url'   => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        'api_key'    => env('ANTHROPIC_API_KEY'),
        // Default to Sonnet for persuasive long-form copy quality; override
        // per-call (e.g. Haiku for cheap structured extraction) or via env.
        'model'      => env('CLAUDE_MODEL', 'claude-sonnet-5-5'),
        'version'    => env('ANTHROPIC_VERSION', '2023-06-01'),
        'timeout'    => (int) env('AI_TIMEOUT', 120),
        'max_tokens' => (int) env('CLAUDE_MAX_TOKENS', 8000),
    ],

];
