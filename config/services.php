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

    'twilio' => [
        'sid'           => env('TWILIO_ACCOUNT_SID'),
        'token'         => env('TWILIO_AUTH_TOKEN'),
        // E.164 number of the Twilio WhatsApp sender, e.g. +14155238886
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
    ],

    'mtn_momo' => [
        'base_url'         => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY', ''),
        'environment'      => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
    ],

    'orange_money' => [
        'base_url'     => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com/orange-money-webpay/cm/v1'),
        'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY', ''),
        'environment'  => env('ORANGE_MONEY_ENVIRONMENT', 'sandbox'),
    ],

];
