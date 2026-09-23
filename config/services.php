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

    'fraud_checker' => [
        'key' => env('FRAUD_CHECKER_API_KEY'),
        'url' => 'https://fraudchecker.link/api/v1/qc/',
    ],

    'bd_courier' => [
        'base_url' => env('BD_COURIER_BASE_URL', 'https://api.bdcourier.com'),
        'api_key'  => env('BD_COURIER_API_KEY', ''),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'epbx' => [
        'base_url'    => env('EPBX_BASE_URL', 'https://ebsbazarbd.epbx.bd/api/v1'),
        'verify_url'  => env('EPBX_VERIFY_URL', 'https://ebsbazarbd.epbx.bd/api/v1/calls/verify'),
        'api_token'   => env('EPBX_API_TOKEN', 'SmjIPNlkdCDZpkXDLJEL7WoASn7ib5xPhhihFvSV'),
        'dialer_url'  => env('EPBX_DIALER_URL', 'https://ebsbazarbd.epbx.bd/dialer'),
    ],

];
