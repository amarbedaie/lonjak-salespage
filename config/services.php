<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // AI salespage generation
    'openrouter' => [
        'key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL', 'anthropic/claude-3.5-haiku'),
    ],

    // BayarCash (Malaysian FPX / DuitNow payment gateway)
    'bayarcash' => [
        'token' => env('BAYARCASH_PAT'),
        'portal_key' => env('BAYARCASH_PORTAL_KEY'),
        'api_secret' => env('BAYARCASH_API_SECRET'),
        'channel' => (int) env('BAYARCASH_CHANNEL', 1),       // 1=FPX, 4=DuitNow OBW, 5=DuitNow QR
        'sandbox' => env('BAYARCASH_SANDBOX', true),
        'api_version' => env('BAYARCASH_API_VERSION', 'v3'),
    ],

    // ZeptoMail (transactional email via HTTP API)
    'zeptomail' => [
        'token' => env('ZEPTOMAIL_TOKEN'),
        'from' => env('ZEPTOMAIL_FROM_ADDRESS'),
        'from_name' => env('ZEPTOMAIL_FROM_NAME', 'Lonjak'),
    ],

];
