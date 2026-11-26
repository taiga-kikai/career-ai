<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// メンテナンス中なら、Laravelを起動する前に案内用ファイルを読み込みます。
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Composerが依存パッケージとアプリのクラスを読み込めるようにします。
require __DIR__.'/../vendor/autoload.php';

// Laravelを起動し、ブラウザーから届いたHTTPリクエストをアプリへ渡します。
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
