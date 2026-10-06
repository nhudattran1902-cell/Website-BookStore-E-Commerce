<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'bookshop_ai' => [
        'api_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'model' => env('GEMINI_MODEL', 'gemini-3.6-flash'),
    ],

    'payments' => [
        'expiry_minutes' => (int) env('PAYMENT_EXPIRY_MINUTES', 15),
        'demo_enabled' => (bool) env('PAYMENT_DEMO_ENABLED', false),
    ],

    'vnpay' => [
        'base_url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'tmn_code' => env('VNPAY_TMN_CODE'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
    ],

    'momo' => [
        'endpoint' => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
        'partner_code' => env('MOMO_PARTNER_CODE'),
        'access_key' => env('MOMO_ACCESS_KEY'),
        'secret_key' => env('MOMO_SECRET_KEY'),
    ],

    'vietqr' => [
        'bank_id' => env('VIETQR_BANK_ID'),
        'account_no' => env('VIETQR_ACCOUNT_NO'),
        'account_name' => env('VIETQR_ACCOUNT_NAME'),
        'signature_key' => env('VIETQR_SIGNATURE_KEY'),
    ],

    'shipping' => [
        'ghn' => [
            'webhook_secret' => env('GHN_WEBHOOK_SECRET'),
        ],
        'ghtk' => [
            'webhook_secret' => env('GHTK_WEBHOOK_SECRET'),
        ],
    ],

];
