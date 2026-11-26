<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

/** アプリ全体で共有する起動時設定を登録する。 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * コンテナ登録が必要な独自サービスを追加する場所。
     */
    public function register(): void
    {
        //
    }

    /**
     * 本番でURLをHTTPSに固定し、プロキシ経由でも安全なリンクを生成する。
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
