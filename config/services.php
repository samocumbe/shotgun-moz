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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | SMS - SHOTGUN-MOZ
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'driver'   => env('SMS_DRIVER', 'log'),        // log | twilio | africastalking
        'api_key'  => env('SMS_API_KEY'),
        'api_url'  => env('SMS_API_URL'),
        'username' => env('SMS_USERNAME'),
        'sender'   => env('SMS_SENDER', 'SHOTGUN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp - SHOTGUN-MOZ
    |--------------------------------------------------------------------------
    */
    'whatsapp' => [
        'driver'       => env('WHATSAPP_DRIVER', 'log'),   // log | twilio | wati | meta
        'api_key'      => env('WHATSAPP_API_KEY'),
        'api_url'      => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'phone_id'     => env('WHATSAPP_PHONE_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
        'app_secret'   => env('WHATSAPP_APP_SECRET'),
    ],
];
