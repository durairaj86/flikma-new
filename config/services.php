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

      'gemini' => [
          'key' => env('GEMINI_API_KEY'),
          // Document scanning (bill/receipt) only, and deliberately configurable:
          // a single AI Studio key can lose access to a model overnight, or the
          // active model can be transiently overloaded, so which model scanning
          // uses is an env setting rather than hardcoded. See
          // GeminiService::scanModel() for the accepted values.
          'scan_quality' => env('GEMINI_SCAN_QUALITY', 'medium'),
      ],

  ];
