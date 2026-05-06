<?php

$env = env('APP_ENV', 'local');
return [
    'LAAR' => [
        'tracking_base_url' => 'https://fenix.laarcourier.com/Tracking',
    ],
    'UHD' => [
        'tracking_base_url' => (
            $env === 'production'
            ? 'https://uhd.urbano.com.ec/client/web/etracking'
            : 'https://devuhd.urbano.com.ec/client/web/etracking'
        ),
    ]
];
