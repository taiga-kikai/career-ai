# Career AI

新卒就活の情報をまとめて管理する、スマートフォン・PC対応のWebアプリです。企業への応募状況、ES、面接準備、予定を一か所で整理できます。

## クイックリンク

| 用途 | リンク |
| --- | --- |
| Career AIのログイン画面 | [アプリを開く](https://career-ai-taiga-demo.onrender.com/) |
| Render（デプロイ・環境変数・ログ） | [サービス管理画面](https://dashboard.render.com/web/srv-dbl85sijnfac73evom90) |
| Neon（PostgreSQL・利用状況） | [Career AIプロジェクト](https://console.neon.tech/app/projects/empty-river-24101974/integrations) |
| Google AI Studio（Gemini API） | [AI Studioを開く](https://aistudio.google.com/prompts/new_chat) |
| Google Cloud（Calendar API・OAuthの監査ログ） | [監査ログを開く](https://console.cloud.google.com/logs/query;query=%2528logName%20%3D%20%22projects%2Fproject-41e84ac5-fd09-440b-b0f%2Flogs%2Fcloudaudit.googleapis.com%252Factivity%22%20OR%20logName%20%3D%20%22projects%2Fproject-41e84ac5-fd09-440b-b0f%2Flogs%2Fcloudaudit.googleapis.com%252Fdata_access%22%20OR%20labels.activity_type_name:*%2529;cursorTimestamp=2026-11-04T17:18:01.898920Z;duration=P7D?authuser=1&project=project-41e84ac5-fd09-440b-b0f) |

Render、Neon、Google Cloudの管理画面には、該当アカウントへのログインと必要な権限が必要です。リンクを知っているだけでアクセス権が得られることはありません。

## 主な機能

- **企業・応募管理**：本選考、夏インターン、秋・冬インターンごとに企業と選考状況を管理します。
- **ES・書類**：応募先ごとに文章を保存し、質問と回答を整理・編集できます。Gemini APIを設定すると、貼り付けた文章の設問整理を補助します。
- **面接対策**：企業ごとに面接準備の内容を記録します。
- **スケジュール**：選考予定や提出締切を管理します。設定すればGoogleカレンダーにも登録できます。
- **企業情報・比較**：自分で入力した給与・休日などを企業ごとに保存し、項目別に比較できます。企業情報の自動検索や総合ランキングは行いません。
- **ホーム画面**：応募状況、締切、直近の予定をまとめて確認できます。
- **招待制アカウント**：招待リンクからアカウントを作成します。

企業、ES、面接準備、予定、企業プロフィールはアカウントごとに分けて保存されます。

## デモの利用について

公開デモは共有のサンプルアカウントで動作します。デモに入力した内容は、他のデモ利用者に見える場合があり、削除・初期化されることがあります。実際の就活情報、氏名、住所、連絡先、パスワードなどは入力しないでください。

GeminiによるES整理を使うと、入力した文章がGemini APIへ送信されます。利用する場合は、個人情報や送信したくない内容を除いてください。Googleカレンダー連携は個人アカウント向けです。共有デモアカウントでは利用できません。

## ローカルで起動する

### 必要なもの

- Git
- PHP 8.3以上と、使用するDBに対応したPHP拡張
- Composer
- Node.jsとnpm
- SQLite、またはMySQL 8.4以上

この手順はWindows PowerShell向けです。SQLiteを使う場合、PHPの`pdo_sqlite`拡張が必要です。MySQLを使う場合は、SQLiteファイルを作成する行を省略し、下記のDB設定をマイグレーション前に行ってください。

```powershell
git clone https://github.com/taiga-kikai/career-ai.git
cd career-ai
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
New-Item -ItemType File -Path database/database.sqlite -Force | Out-Null
php artisan key:generate
npm.cmd install
npm.cmd run build
php artisan migrate --seed
php artisan serve
```

起動後、同じPCのブラウザーで [http://127.0.0.1:8000](http://127.0.0.1:8000) を開きます。ローカル環境ではログイン画面の「デモでログイン」からサンプルデータを確認できます。サーバーを止めるときはPowerShellで`Ctrl+C`を押します。

### MySQLを使う場合

MySQLに開発用データベースを作成し、`.env`のDB設定を次のようにします。データベース名・ユーザー名・パスワードは自分の環境に合わせてください。

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=career_ai
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

設定を変更した後は、次を実行してからマイグレーションします。

```powershell
php artisan config:clear
php artisan migrate --seed
```

## 外部サービスの設定

必要な機能だけ設定してください。秘密情報は`.env`またはホスティングサービスの環境変数に保存し、GitHubへ登録しないでください。

| 環境変数 | 用途 |
| --- | --- |
| `GEMINI_API_KEY` | ES・書類の設問整理 |
| `GEMINI_MODEL` | Geminiの使用モデル。未設定時はアプリの既定値を使います |
| `GOOGLE_CALENDAR_CLIENT_ID`、`GOOGLE_CALENDAR_CLIENT_SECRET` | Googleカレンダー連携 |
| `GOOGLE_CALENDAR_REDIRECT_URI` | Google OAuthのコールバックURL |
| `DB_URL` | PostgreSQLの接続URL。RenderでNeonを使う場合に設定します |
| `CAREER_AI_OWNER_EMAIL` | ホームのDB・CU利用状況カードを表示するアカウント |

Googleカレンダーを使うには、Google Cloud側でOAuthクライアントを設定し、アプリのURLに対応するコールバックURLを登録してください。Gemini APIキーやDB接続URLは、公開リポジトリのファイル・Issue・スクリーンショットへ載せないでください。

## 公開デモの構成

RenderのBlueprint設定はリポジトリ直下の`render.yaml`にあります。GitHubの`main`ブランチをRenderへ接続すると、更新をデプロイできます。

`DB_URL`を設定しない場合、Renderでは一時SQLiteを使います。このデータは再起動や再デプロイで失われる可能性があります。長期保存が必要な環境ではPostgreSQLを設定してください。無料プランの稼働時間・容量・停止条件は提供元により変わるため、最新の上限とバックアップ条件を各管理画面で確認してください。ホームの容量表示はアプリが取得したDB論理容量です。FreeプランのCU時間はCareer AIからのDB接続記録を使った推定値で、Neon管理画面や他のアプリからの利用は含まれません。実際の使用量はNeon管理画面で確認してください。

## 開発時のDBと公開DB

ローカル版は`.env`で選んだデータベースを使い、公開版とは別に動作します。たとえば、ローカルではMySQL、RenderではNeonのPostgreSQLを使う構成にできます。この場合、ローカルでの操作は公開DBへ自動同期されません。

## バージョン履歴

変更はGitのコミットとタグで記録しています。リリースタグはGitHubの[Tags](https://github.com/taiga-kikai/career-ai/tags)から確認できます。

<details>
<summary>v1.00〜v1.33の変更内容</summary>

| バージョン | 主な変更 |
| --- | --- |
| v1.00 | 初回公開版 |
| v1.01 | SQLiteのマイグレーションを修正 |
| v1.02 | Renderのヘルスチェックを修正 |
| v1.03 | Renderの暗号化キー設定を修正 |
| v1.04 | 公開デモURLをREADMEに追加 |
| v1.05 | HTTPS表示とログイン送信を修正 |
| v1.06 | Renderから外部PostgreSQLへ接続 |
| v1.07 | アカウントデータの移行コマンドを追加 |
| v1.08 | ホームにNeon利用状況カードを追加 |
| v1.09 | 全アカウントのデータをNeonへ移行可能に変更 |
| v1.10 | 移行先にある空のデモアカウントを再利用 |
| v1.11 | Render向けGoogle Calendar OAuth設定を追加 |
| v1.12 | Neon Free向けCU推定ゲージを追加 |
| v1.13 | Render・Neonの管理画面リンクを整理 |
| v1.14 | PCでのローカル起動手順を追加 |
| v1.15 | Google AI StudioとGoogle Cloud監査ログのリンクを追加 |
| v1.16 | 予定の種類に応じた日時入力に変更 |
| v1.17 | 企業・応募管理とスケジュールを連携 |
| v1.18 | 結果待ちの予定日とカレンダーの色分けを追加 |
| v1.19 | 複数日予定の連続表示を試行 |
| v1.20 | カレンダー配置を調整 |
| v1.21 | カレンダー表示をv1.18の形式に復帰 |
| v1.22 | 複数日予定バーの表示を試行 |
| v1.23 | 複数日予定バーを保留し、カレンダーをv1.18の形式に復帰 |
| v1.24 | ホームのDB/CUカード配置とミーアキャットの案内を追加 |
| v1.25 | 応募予定数とホームのカード配置を調整 |
| v1.26 | Gemini APIエラーの安全なログ記録を追加 |
| v1.27 | ES設問整理の改善とAPIエラー記録を追加 |
| v1.28 | ES整理中の表示と二重送信防止を追加 |
| v1.29 | 企業研究AIを終了し、企業情報の編集・項目別ランキングを追加 |
| v1.30 | READMEを公開向けに整理 |
| v1.31 | 完了済み予定を除外し、ホームに近い予定を最大3件表示 |
| v1.32 | ホームの締切・予定一覧を中央の空きスペースへ移動 |
| v1.33 | 予定日時の初期値・カレンダー選択日を修正 |

</details>

## ライセンス

このリポジトリには`LICENSE`ファイルがありません。公開リポジトリであることは、ソフトウェアの再利用を許可するライセンスがあることを意味しません。
