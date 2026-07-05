<?php

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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    's3' => [
        'url' => env('AWS_S3_URL','https://bokit-app.s3.eu-north-1.amazonaws.com'),
        'bucket' => env('AWS_BUCKET','bokit-app'),
    ],
    'storage' => [
        'url' => env('ASSET_STORAGE_URL', env('AWS_S3_URL', 'https://bokit-app.s3.eu-north-1.amazonaws.com')),
    ],
    'sms' => [
        'environment' => env('SMS_ENVIRONMENT',1),// 1 = live, 2 = test
        'username' => env('SMS_USERNAME','a61cb53f-8b66-474b-9cb9-8044decb4d3f'),
        'password' => env('SMS_PASSWORD','d5b03150e4ae915b8b0891439b786559518a9c4d1d75d00ac4149c82363e849f'),
        'sender' => env('SMS_SENDER','d60e33a3f97b3927989e95bee7d7780f46589d1b86cc2f674997645fd37529d0'),
        'base_url_otp' => env('SMS_BASE_URL_OTP','https://smsmisr.com/api/OTP/'),
        'template_token' => env('SMS_TEMPLATE_TOKEN','e83faf6025ec41d0f40256d2812629f5fa9291d05c8322f31eea834302501da8'),
    ],

    'chataman' => [
        'base_url' => env('CHATAMAN_BASE_URL', 'https://chataman.com'),
        'token' => env('CHATAMAN_ACCESS_TOKEN'),
        'token_header' => env('CHATAMAN_TOKEN_HEADER', 'Authorization'),
        'token_prefix' => env('CHATAMAN_TOKEN_PREFIX', 'Bearer'),
        'timeout' => env('CHATAMAN_TIMEOUT', 15),
        'otp_template_name' => env('CHATAMAN_OTP_TEMPLATE_NAME', 'otp_el7lmplatform'),
        'otp_template_language' => env('CHATAMAN_OTP_TEMPLATE_LANGUAGE', 'ar'),
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ],

];
