<?php

return [

    'smspoh' => [
        'token' => env('SMSPOH_SMS_TOKEN'),
        'sender' => env('SMSPOH_SENDER', 'MTF'),
        'base_url' => env('SMSPOH_BASE_URL', 'https://smspoh.com/api/v2'),
    ],
];
