<?php

// Sambungan ke INFRA (otak pemasaran BeDaie). Dimatikan secara lalai.
return [
    'url' => rtrim((string) env('INFRA_URL', ''), '/'),
    'token' => (string) env('INFRA_TOKEN', ''),
    'aktif' => (bool) env('INFRA_PULL_ENABLED', false),
];
