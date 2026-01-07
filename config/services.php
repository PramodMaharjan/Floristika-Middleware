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

    'shopify' => [
        'domain' => env('SHOPIFY_DOMAIN'),
        'access_token' => env('SHOPIFY_ACCESS_TOKEN'),
        'api_secret' => env('SHOPIFY_API_SECRET'),
        'api_version' => env('SHOPIFY_API_VERSION'),
        'webhook_secret' => env('SHOPIFY_WEBHOOK_SECRET'),
    ],
    
    'lineclear' => [
        'uat_base' => env('LINECLEAR_UAT_BASE'),
        'prod_base' => env('LINECLEAR_PROD_BASE'),
        'pod_base'  => env('LINECLEAR_POD_BASE'),
        'viewtrack_base' => env('LINECLEAR_VIEWTRACK_BASE'),
        'api_key' => env('LINECLEAR_API_KEY'),
        'client_id' => env('LINECLEAR_CLIENT_ID'),
        'client_secret' => env('LINECLEAR_CLIENT_SECRET'),
        'token' => env('LINECLEAR_TOKEN'),
        'waybill_token' => env('LINECLEAR_WAYBILL_TOKEN'),
        'username'       => env('LINECLEAR_USERNAME'),
        'password'       => env('LINECLEAR_PASSWORD'),
    ] ,

    'lalamove' => [
        'uat_base'      => env('LALAMOVE_UAT_BASE'),
        'prod_base'     => env('LALAMOVE_PROD_BASE'),
        'api_key'       => env('LALAMOVE_API_KEY'),
        'secret'        => env('LALAMOVE_SECRET'),
        'api_key_test'  => env('LALAMOVE_API_KEY_TEST'),
        'secret_test'   => env('LALAMOVE_SECRET_TEST'), 
        'market'        => env('LALAMOVE_MARKET'),
    ],

    'detrack' => [
        'base' => env('DETRACK_BASE_URL'),
        'api_key' => env('DETRACK_API_KEY')
    ]

];
