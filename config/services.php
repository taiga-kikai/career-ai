<?php

return [

    // Career AI固有の表示・機能フラグです。VERSIONはログイン画面のバージョン表示に使います。
    'career_ai' => [
        'web_enabled' => (bool) env('CAREER_AI_WEB_ENABLED', false),
        'version' => is_file(base_path('VERSION')) ? trim(file_get_contents(base_path('VERSION'))) : 'dev',
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

    // ES設問整理から使うGeminiの秘密キーとモデル名です。秘密キーは.env/ホスティング環境変数に置きます。
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-3.5-flash-lite'),
    ],

    // Google OAuthのクライアント情報と、Googleから戻るURLです。
    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_CALENDAR_REDIRECT_URI', rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/').'/google-calendar/callback'),
    ],
    // 管理画面のDB容量・CU表示に使う設定です。APIキーは公開ファイルへ記載しません。
    'neon' => [
        'owner_email' => env('CAREER_AI_OWNER_EMAIL'),
        'plan' => env('NEON_PLAN', 'free'),
        'storage_limit_bytes' => env('NEON_STORAGE_LIMIT_BYTES', 1073741824),
        'monthly_cu_limit' => env('NEON_MONTHLY_CU_LIMIT', 100),
        'estimated_min_cu' => env('NEON_ESTIMATED_MIN_CU', 0.25),
        'estimated_suspend_seconds' => env('NEON_ESTIMATED_SUSPEND_SECONDS', 300),
        'api_key' => env('NEON_API_KEY'),
        'org_id' => env('NEON_ORG_ID'),
        'project_id' => env('NEON_PROJECT_ID'),
    ],

];
