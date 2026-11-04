# Career AI

新卒就活の企業・応募管理、ES・書類、面接対策、企業研究、スケジュールをまとめるLaravel製Webアプリです。画面はスマートフォンとPCの両方に対応しています。

## ローカル開発

必要なもの: PHP 8.3以上、Composer、Node.js/npm、MySQL 8.4（またはSQLite）。

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm.cmd install
npm.cmd run build
php artisan migrate --seed
php artisan serve
```

`.env` にGemini APIキーを設定すると企業研究AIを使えます。Google Calendar連携はOAuthの認証情報を設定したローカル環境で有効にしてください。`.env` と実データベースはGitに含めません。

## アカウントとデータ

招待リンクからアカウントを作成します。企業、ES、面接対策、企業研究、予定はアカウントごとに分離して保存されます。公開デモは共有サンプルアカウントで、入力データは他のデモ利用者から見える場合があり、消去されることがあります。デモには個人情報や実際の就活情報を入力しないでください。

## Render無料デモ

リポジトリ直下の `render.yaml` を使うBlueprintデプロイに対応しています。Render側でGitHubリポジトリを選択し、デプロイ後に必要なら `GEMINI_API_KEY` をサービスの環境変数へ登録してください。Google Calendar連携は公開デモでは無効です。

無料サービスはアクセスがないと停止し、次回表示時に起動待ちが発生します。データベースは一時領域のSQLiteを使うため、再起動・再デプロイでサンプル内容が初期化されることがあります。個人利用データの保管には使わず、ローカルのMySQLを利用してください。

## バージョン

リリースはGitタグで管理します。初回公開版は `v1.00`、SQLiteの移行修正版は `v1.01`、Renderヘルスチェック修正版は `v1.02` です。今後も変更内容を記録してタグを追加します。
