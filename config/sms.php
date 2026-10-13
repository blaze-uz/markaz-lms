<?php

return [
    'driver' => env('SMS_DRIVER', 'log'),

    'eskiz' => [
        'base_url' => env('ESKIZ_BASE_URL', 'https://notify.eskiz.uz'),
        'email' => env('ESKIZ_EMAIL'),
        'password' => env('ESKIZ_PASSWORD'),
        'from' => env('ESKIZ_FROM', '4546'),
        'callback_token' => env('ESKIZ_CALLBACK_TOKEN'),
    ],
];
