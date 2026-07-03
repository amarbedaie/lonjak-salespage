<?php

return [
    // Master switch — nothing sends unless true.
    'enabled' => (bool) env('WHATSAPP_NOTIFICATIONS_ENABLED', false),

    // Fallback merchant number when a merchant has not set users.notify_whatsapp.
    'admin_number' => env('MENDAP_ADMIN_WHATSAPP'),
];
