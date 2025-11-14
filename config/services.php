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

    /*
    |--------------------------------------------------------------------------
    | Trendyol API Configuration
    |--------------------------------------------------------------------------
    | Trendyol Marketplace API için gerekli bilgiler
    | API Dokümantasyonu: https://developers.trendyol.com
    */
    'trendyol' => [
        'environment' => env('TRENDYOL_ENVIRONMENT', 'production'), // 'production' or 'stage'
        'api_key' => env('TRENDYOL_API_KEY'),
        'api_secret' => env('TRENDYOL_API_SECRET'),
        'seller_id' => env('TRENDYOL_SELLER_ID'),
        'supplier_id' => env('TRENDYOL_SUPPLIER_ID'), // Legacy support
        'base_uri' => env('TRENDYOL_BASE_URI', 'https://api.trendyol.com/sapigw'),
        'stage_base_uri' => 'https://stageapi.trendyol.com/sapigw',
    ],

    /*
    |--------------------------------------------------------------------------
    | Netgsm SMS API Configuration
    |--------------------------------------------------------------------------
    | Netgsm SMS servisi için gerekli bilgiler
    | API Dokümantasyonu: https://www.netgsm.com.tr/dokuman/
    */
    'netgsm' => [
        'api_url' => env('NETGSM_API_URL', 'https://api.netgsm.com.tr'),
        'username' => env('NETGSM_USERNAME'),
        'password' => env('NETGSM_PASSWORD'),
        'header' => env('NETGSM_HEADER', 'MARKETPLACE'), // SMS başlığı
    ],

];
