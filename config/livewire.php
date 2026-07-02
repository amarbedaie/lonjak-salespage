<?php

return [
    /*
     * Raised limits so Pustaka Media can accept short video clips (default is 12MB).
     * Other Livewire settings keep their package defaults via config merge.
     */
    'temporary_file_upload' => [
        'disk' => null,
        'rules' => ['required', 'file', 'max:112640'], // 110MB ceiling at the temp-upload layer
        'directory' => null,
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4', 'mov', 'avi', 'wmv', 'mp3', 'm4a', 'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 10,
        'cleanup' => true,
    ],
];
