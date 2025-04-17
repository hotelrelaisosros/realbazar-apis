<?php

return [
    'test' => [
        'sk' => env('STRIPE_TEST_SK'),
        'pk' => env('STRIPE_TEST_PK'),
        'webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET'),
    ],
    'live' => [
        'sk' => env('STRIPE_LIVE_SK'),
        'pk' => env('STRIPE_LIVE_PK'),
        'webhook_secret' => env('STRIPE_LIVE_WEBHOOK_SECRET'),

    ]
];
