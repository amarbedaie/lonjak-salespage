<?php

return [
    // Master switch — nothing is provisioned unless true.
    'enabled' => (bool) env('PROVISIONING_ENABLED', false),

    // Universal BeDaie credentials — the SAME pair is configured on every target system.
    'key' => env('PROVISIONING_API_KEY'),
    'secret' => env('PROVISIONING_SIGNING_SECRET'),

    'timeout' => (int) env('PROVISIONING_TIMEOUT', 20),

    /*
    | Target systems. The key is what gets stored on salespages.provision_system;
    | the URL is env-overridable so a staging → production domain swap needs no code change.
    | Every endpoint speaks the same contract (see docs/provisioning.md).
    */
    'systems' => [
        'tafsirsurah' => [
            'name' => 'Tafsir Surah',
            'url' => env('PROVISION_URL_TAFSIRSURAH', 'https://tafsirsurah.com/api/provision'),
        ],
        'statushadis' => [
            'name' => 'Status Hadis',
            'url' => env('PROVISION_URL_STATUSHADIS', 'https://statushadis.com/api/provision'),
        ],
        'hafalku' => [
            'name' => 'HafalKu',
            'url' => env('PROVISION_URL_HAFALKU', 'https://hafalku.129-212-216-31.sslip.io/api/provision'),
        ],
        'darah-wanita' => [
            'name' => 'Darah Wanita',
            'url' => env('PROVISION_URL_DARAH_WANITA', 'https://darah-wanita.129-212-216-31.sslip.io/api/provision'),
        ],
        'tibb-nabawi' => [
            'name' => 'Tibb Nabawi',
            'url' => env('PROVISION_URL_TIBB_NABAWI', 'https://tibbnabawi.129-212-216-31.sslip.io/api/provision'),
        ],
        'arabi' => [
            'name' => 'Arabi.my',
            'url' => env('PROVISION_URL_ARABI', 'https://arabi.my/api/provision'),
        ],
        'peguamai' => [
            'name' => 'PeguamAI',
            'url' => env('PROVISION_URL_PEGUAMAI', 'https://peguamai.129-212-216-31.sslip.io/api/provision'),
        ],
        'os-bedaie' => [
            'name' => 'os BEDAIE',
            'url' => env('PROVISION_URL_OS_BEDAIE', 'https://osbedaie.129-212-216-31.sslip.io/api/provision'),
        ],
        'bykitab' => [
            'name' => 'byKitab',
            'url' => env('PROVISION_URL_BYKITAB', 'https://bykitab.com/api/provision'),
        ],
        'bijak-bi' => [
            'name' => 'Bijak BI',
            'url' => env('PROVISION_URL_BIJAK_BI', 'https://bijak-bi.pages.dev/api/provision'),
        ],
    ],
];
