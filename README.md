# Career AI

新卒就活の企業・応募管理、ES・書類、面接対策、企業研究、スケジュールをまとめるLaravel製Webアプリです。画面はスマートフォンとPCの両方に対応しています。

## クイックリンク

| 用途 | リンク |
| --- | --- |
| Career AIのログイン画面 | [アプリを開く](https://career-ai-taiga-demo.onrender.com/) |
| Render（デプロイ・環境変数・ログ） | [サービス管理画面](https://dashboard.render.com/web/srv-dbl85sijnfac73evom90) |
| Neon（PostgreSQL・利用状況） | [Career AIプロジェクト](https://console.neon.tech/app/projects/empty-river-24101974/integrations) |
| Google AI Studio（Gemini API） | [AI Studioを開く](https://aistudio.google.com/prompts/new_chat) |
| Google Cloud（Calendar API・OAuthの監査ログ） | [監査ログを開く](https://console.cloud.google.com/logs/query;query=%2528logName%20%3D%20%22projects%2Fproject-41e84ac5-fd09-440b-b0f%2Flogs%2Fcloudaudit.googleapis.com%252Factivity%22%20OR%20logName%20%3D%20%22projects%2Fproject-41e84ac5-fd09-440b-b0f%2Flogs%2Fcloudaudit.googleapis.com%252Fdata_access%22%20OR%20labels.activity_type_name:*%2529;cursorTimestamp=2026-11-04T17:18:01.898920Z;duration=P7D?authuser=1&project=project-41e84ac5-fd09-440b-b0f) |

RenderとNeonの管理画面を開くには、それぞれのアカウントへのログインとプロジェクト権限が必要です。このリンクは管理画面への入口であり、ログイン情報や権限を共有するものではありません。公開リポジトリにはパスワード、APIキー、DB接続URLなどの秘密情報を登録しないでください。

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

### PCでローカル版を起動してテスト

初期設定が終わった後は、毎回以下の手順で起動します。

1. VS Codeで `C:\Users\taiga\career-ai` フォルダーを開く
2. メニューの「ターミナル」→「新しいターミナル」を選ぶ
3. PowerShellでプロジェクトフォルダーにいることを確認し、次を実行する

```powershell
php artisan serve
```

4. 表示されたら、同じPCのブラウザーで [http://127.0.0.1:8000/](http://127.0.0.1:8000/) を開く

PowerShellのウィンドウは起動中そのままにします。停止するときはターミナルで `Ctrl+C` を押します。ローカル版は `.env` のDB設定を使うため、`DB_CONNECTION=mysql` ならローカルMySQLに接続し、Render上の公開版やNeonとは別に動作します。

`.env` にGemini APIキーを設定すると企業研究AIを使えます。Google Calendar連携はOAuthの認証情報を設定したローカル環境で有効にしてください。`.env` と実データベースはGitに含めません。

## アカウントとデータ

招待リンクからアカウントを作成します。企業、ES、面接対策、企業研究、予定はアカウントごとに分離して保存されます。公開デモは共有サンプルアカウントで、入力データは他のデモ利用者から見える場合があり、消去されることがあります。デモには個人情報や実際の就活情報を入力しないでください。

## Render無料デモ

公開URL: [https://career-ai-taiga-demo.onrender.com](https://career-ai-taiga-demo.onrender.com)

リポジトリ直下の `render.yaml` を使うBlueprintデプロイに対応しています。Render側でGitHubリポジトリを選択し、デプロイ後に必要なら `GEMINI_API_KEY` をサービスの環境変数へ登録してください。Google Calendar連携を使うには、Renderの秘密環境変数 `GOOGLE_CALENDAR_CLIENT_ID` と `GOOGLE_CALENDAR_CLIENT_SECRET` にGoogle OAuthクライアント情報を設定し、Google Cloud Consoleの承認済みリダイレクトURIに `https://career-ai-taiga-demo.onrender.com/google-calendar/callback` を登録します。締切・予定の色ラベル（ユーカリ・トマト・グラファイト）の利用にはカレンダー情報の更新権限も必要です。この更新を取り込むため、既に連携済みの場合は一度連携を解除してから再連携してください。Google OAuthの公開ステータスがテスト中の場合、テストユーザーに追加したGoogleアカウントのみ連携でき、認可は7日で期限切れになります。一般公開して期限制限をなくすには、OAuthアプリ公開とGoogleの必要な確認手続きが別途必要です。共有デモアカウントでは個人のGoogleカレンダーを誤接続しないよう、この連携機能を使用できません。個人アカウントでログインして連携してください。PostgreSQLを使う場合は、Renderの秘密環境変数 `DB_URL` に接続URLを設定します。`DB_URL` がなければ一時SQLiteを使います。

無料サービスはアクセスがないと停止し、次回表示時に起動待ちが発生します。初期設定では一時SQLiteを使うため、再起動・再デプロイでデータが消える可能性があります。個人データを長期保存する場合はNeonなど外部PostgreSQLを用意し、Renderの DB_URL に秘密情報として設定してください。無料プランには容量・利用量の上限やバックアップ条件があるため、重要データは別途定期保存してください。

## ローカルMySQLからPostgreSQLへの一回移行

先にPostgreSQLの接続先URLをローカルの `.env` に `CAREER_AI_TRANSFER_DB_URL` として設定します。既存の `DB_CONNECTION=mysql` は変更しません。接続先URLをGitHubやチャットへ貼らないでください。設定後に以下を実行します。

~~~powershell
php artisan config:clear
php artisan migrate --database=career_ai_transfer
php artisan career-ai:transfer-account --all-users
~~~

移行先に同じメールアドレスのアカウントがある場合は、空のデモアカウントだけ再利用し、それ以外は重複防止のためコピー全体を中止します。既存デモアカウントにデータや招待がある場合も中止します。全ユーザーと紐づく就活データ、招待情報をコピーします。パスワードハッシュは引き継ぎますが、ログインセッション、Remember token、Google Calendarの暗号化認証情報、予定のGoogleイベントIDは移しません。Google Calendarは移行後に再連携してください。件数を確認し、移行が終わったらローカル `.env` の一時URLを削除し、Renderの `DB_URL` を同じPostgreSQLに設定します。
## Neon利用状況カード

ホームの利用状況カードは、CAREER_AI_OWNER_EMAILに設定したアカウントだけに表示されます。保存量はPostgreSQLへ接続中にページを開くか「更新」を押すと取得します。FreeプランではNeonのCU実績APIを使えないため、Career AI経由の認証済みリクエストでDB接続時刻を記録し、最小CUと自動停止時間を仮定したCU推定ゲージを表示します。Neon管理画面や他のアプリなどCareer AI以外からの利用、実際のオートスケール量は含まれないため、推定値として扱い、実測はNeon管理画面で確認してください。有料プランでは必要に応じてRenderの環境変数NEON_API_KEY、NEON_ORG_ID、NEON_PROJECT_IDを設定すると、Neon APIからCU実績を読み込みます（約15分間隔）。これらは秘密情報なのでGitHubへ登録しません。

FreeプランのCUゲージと残り稼働時間は、Career AIのDB接続時間を `NEON_ESTIMATED_MIN_CU`（初期値0.25 CU）と `NEON_ESTIMATED_SUSPEND_SECONDS`（初期値300秒）で概算した値です。月のCU上限は `NEON_MONTHLY_CU_LIMIT`（初期値100）です。推定器は認証済みのアプリリクエストごとに月次記録を更新するため、Neonへ小さな追加書き込みが発生します。容量ゲージはDB内部の論理容量の目安で、Neonの課金上の正確なストレージ使用量とは異なる場合があります。DB内の保存量を減らしてもCU使用量が直接減るわけではありません。プランや上限の変更はNeon管理画面で行います。

## 開発DBと公開DB

ローカル開発時は `.env` の `DB_CONNECTION=mysql` とローカルMySQLを使い、RenderだけNeonのPostgreSQLに接続する構成にできます。NeonのCU時間はNeonのコンピュートが稼働した時間に応じて消費されるため、ローカルでMySQLだけを使っている間は開発操作でNeonのCU時間は増えません。Neonの容量・CU表示を確認するときや公開アプリへアクセスしたときはNeonを利用します。Neon Freeはプロジェクトごとに月100 CU時間、1 GBのDB容量が含まれます。アプリ内の上限表示もこの値を初期値として設定しています。

## バージョン

リリースはGitタグで管理します。初回公開版は `v1.00`、SQLiteの移行修正版は `v1.01`、Renderヘルスチェック修正版は `v1.02`、Renderの暗号化キー修正版は `v1.03`、公開デモURLをREADMEに追加した版は `v1.04`、RenderのHTTPS表示・ログイン送信修正版は `v1.05`、Renderから外部PostgreSQLへ接続する対応版は `v1.06`、本人アカウントのデータ移行コマンドを追加した版は v1.07、ホームのNeon利用状況カードを追加した版は `v1.08`、ローカルMySQLから全アカウントをNeonへ移行できるようにした版は `v1.09`、Neonが作る空のデモアカウントを再利用する修正版は `v1.10`、公開Render用Google Calendar OAuth設定を追加した版は `v1.11`、Freeプラン向けCU推定ゲージを追加した版は `v1.12`、Render・Neonの管理画面リンクをREADMEに整理した版は `v1.13`、PCでのローカル起動手順をREADMEに追加した版は `v1.14`、Google AI StudioとGoogle Cloud監査ログへのリンクを追加した版は `v1.15`、予定の種類に応じて日時入力を切り替えるようにした版は `v1.16`、企業・応募管理とスケジュールの相互リンクを追加した版は `v1.17`、結果待ちの結果予定日と締切・予定・確定の色分けを追加した版は `v1.18`、複数日予定の連続表示を試行した版は `v1.19`、カレンダー配置を調整した版は `v1.20`、カレンダー表示をv1.18へ戻した版は `v1.21`、日付マスを維持してグレーの複数日予定バーだけを横断表示する試行版は `v1.22`、複数日予定バーを保留してカレンダーをv1.18へ戻した版は `v1.23` です。今後も変更内容を記録してタグを追加します。
