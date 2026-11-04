<?php

return [

    'career_ai' => [
        'web_enabled' => (bool) env('CAREER_AI_WEB_ENABLED', false),
        'demo_login_enabled' => (bool) env('CAREER_AI_DEMO_LOGIN_ENABLED', false),
        'google_calendar_enabled' => (bool) env('CAREER_AI_GOOGLE_CALENDAR_ENABLED', env('APP_ENV', 'production') === 'local'),
    ],

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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
    ],

    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_CALENDAR_REDIRECT_URI', 'http://127.0.0.1:8000/google-calendar/callback'),
    ],
    'neon' => [
        'owner_email' => env('CAREER_AI_OWNER_EMAIL'),
        'plan' => env('NEON_PLAN', 'free'),
        'storage_limit_bytes' => env('NEON_STORAGE_LIMIT_BYTES', 1073741824),
        'monthly_cu_limit' => env('NEON_MONTHLY_CU_LIMIT', 100),
        'api_key' => env('NEON_API_KEY'),
        'org_id' => env('NEON_ORG_ID'),
        'project_id' => env('NEON_PROJECT_ID'),
    ],

];
