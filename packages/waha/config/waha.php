<?php

return [
    'url' => env('WAHA_URL'),
    'api_key' => env('WAHA_API_KEY'),
    'session' => env('WAHA_SESSION', 'default'),
    'default_cc' => env('WAHA_DEFAULT_CC', '60'),
    'timeout' => (int) env('WAHA_TIMEOUT', 15),
];
