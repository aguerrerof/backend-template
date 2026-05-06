<?php

$env = env('APP_ENV', 'local');
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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'payment_gateway' => [
        'credentials' => 'app/pagoplux/'.($env === 'production'
                ? 'production-credentials.json'
                : 'testing-credentials.json'),
    ],
    'shop' => ['base_url' => env('SHOP_BASE_URL', '')],
    'shopify' => [
        'store_url' => env('SHOPIFY_STORE_URL', ''),
        'api_token' => env('SHOPIFY_STOREFRONT_API_TOKEN', ''),
        'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
        'api_version' => env('SHOPIFY_API_VERSION', '2023-07'),
        'id_home_screen' => env('SHOPIFY_ID_HOME_SCREEN_COLLECTION', 'gid://shopify/Collection/300968018059'),
    ],
    'uva_cloud' => [
        'base_url' => env('UVA_CLOUD_BASE_URL', ''),
        'api_key' => env('UVA_CLOUD_API_KEY', ''),
    ]
];
