# Career AI ファイル案内

初めてリポジトリを開いた人が、どのファイルに何があり、どこから読めば機能の仕組みを追えるかをまとめています。ファイル名をクリックするとGitHub上のソースを開けます。

## まず読む場所

| 目的 | ファイル |
| --- | --- |
| アプリ概要・起動・設定 | [README.md](../README.md) |
| 最近の変更履歴 | [CHANGELOG.md](../CHANGELOG.md) |
| URLと処理の対応 | [routes/web.php](../routes/web.php) |
| 応募先の処理 | [ApplicationManagementPreviewController.php](../app/Http/Controllers/ApplicationManagementPreviewController.php) |
| DBの表と列 | [database/migrations](../database/migrations) |
| バージョン | [VERSION](../VERSION) |

## 画面からDBまでの流れ

~~~mermaid
flowchart LR
    A[ブラウザー] --> B[routes/web.php URLと処理の対応]
    B --> C[Controller 入力確認と進行]
    C --> D[Model データの読み書き]
    D --> E[(SQLite / MySQL / PostgreSQL)]
    C --> F[Blade HTML画面]
    F --> G[CSS / JavaScript]
    C --> H[外部サービス Gemini / Google Calendar / Neon]
~~~

例としてESを保存する場合、ルートがURLを担当コントローラーへ渡し、コントローラーが入力を確認します。モデル経由でDBへ保存し、Bladeが画面を作ります。Controllerは「処理の進行役」、Modelは「DBデータの窓口」、Migrationは「DB表の設計書」です。BladeはLaravelの画面テンプレート、Middlewareは画面処理の前後に共通で行う処理、Serviceは外部APIや共通処理をまとめたものです。

## 機能ごとに読む順番

| 機能 | 読む順番 |
| --- | --- |
| ログイン・招待 | routes/web.php → AuthController.php / InvitationController.php → User.php / UserInvitation.php → welcome.blade.php / accept-invitation.blade.php |
| 企業・応募管理 | routes/web.php → ApplicationManagementPreviewController.php → CareerApplication.php → 応募関連Migration → applications.blade.php |
| ES・書類 | ApplicationDocumentController.php → EsQuestionParser.php → ApplicationDocument.php → application_documentsのMigration → documents/のBlade |
| 面接対策 | InterviewPreparationController.php → InterviewPreparation.php → interview_preparationsのMigration → interviews/のBlade |
| 企業一覧・ランキング | CompanyDirectoryController.php → CompanyProfile.php → create_company_profiles_tableのMigration → research/のBlade |
| スケジュール | ScheduleController.php → ScheduleEvent.php → schedule_eventsのMigration → schedule/のBlade |
| Googleカレンダー | GoogleCalendarController.php → GoogleCalendarService.php → User.php / ScheduleEvent.php |
| Neon利用状況 | TrackNeonUsageEstimate.php → NeonActivityEstimateService.php / NeonUsageService.php → neon-usage-card.blade.php |

## PHPファイル：処理とデータ

### コントローラー

| ファイル | 内容 |
| --- | --- |
| [Controller.php](../app/Http/Controllers/Controller.php) | 他のコントローラーが継承する共通の土台です。 |
| [AuthController.php](../app/Http/Controllers/AuthController.php) | showLoginはログイン画面、authenticateはPIN照合と試行回数制限、demoはデモログイン、logoutはセッション破棄を担当します。認証後はセッションIDも更新します。 |
| [InvitationController.php](../app/Http/Controllers/InvitationController.php) | createは期限付き招待リンクの発行、acceptはリンク確認画面、storeは入力検証とユーザー登録を担当します。validInvitationが期限切れ・使用済みリンクを弾きます。 |
| [HomeController.php](../app/Http/Controllers/HomeController.php) | 1つの入口（__invoke）で応募数、近い予定や締切、未整理書類などを集めてホーム画面へ渡します。Neon利用状況も取得します。 |
| [ApplicationManagementPreviewController.php](../app/Http/Controllers/ApplicationManagementPreviewController.php) | __invokeは一覧、store/update/destroyは応募先の追加・更新・削除、destroyStatusOptionは選考状況の選択肢削除を担当します。入力検証、プロフィール同期、応募から予定を作る補助処理も含みます。更新時はDBトランザクションを使い、診断用に処理段階やSQLSTATEなどを記録します。名前にPreviewが残っていますが現行の応募管理画面の処理先です。 |
| [ApplicationDocumentController.php](../app/Http/Controllers/ApplicationDocumentController.php) | index/create/parse/store/showApplication/edit/update/destroyが一覧・フォーム・AI整理・保存・表示・編集・削除を分担します。validateDocumentで入力を確認し、cleanQuestionsで設問と回答を整えます。 |
| [InterviewPreparationController.php](../app/Http/Controllers/InterviewPreparationController.php) | index/showが一覧と詳細、saveが入力検証と保存、destroyが準備内容の削除を担当します。質問・回答は空の行を除いて保存します。 |
| [CompanyDirectoryController.php](../app/Http/Controllers/CompanyDirectoryController.php) | indexが一覧と項目別ランキング、editが企業情報フォーム、updateが入力検証と保存を担当します。入力済みの値だけを比較し、残業は小さい順、他の数値は大きい順です。自動検索やAI調査はしません。 |
| [ScheduleController.php](../app/Http/Controllers/ScheduleController.php) | index/create/store/edit/update/destroyが予定の表示と登録・編集・削除、toggleCompletedが完了切替を担当します。validateEventが入力を確認し、eventDataが保存形式へ変換します。googleCalendarFallbackは手動登録リンクを作ります。 |
| [GoogleCalendarController.php](../app/Http/Controllers/GoogleCalendarController.php) | connect/callbackがOAuthの開始と戻り、disconnectが連携解除、storeEventが予定登録を担当します。認証状態を確認し、共有デモからの連携を制限します。 |

### その他のPHP処理

| ファイル | 内容 |
| --- | --- |
| [EsQuestionParser.php](../app/EsQuestionParser.php) | 貼り付けたES本文を設問と回答に分けます。設定があればGemini APIを使い、利用不可・失敗時は簡易的なローカル整理に切り替えます。GoogleのHTTP状態、エラーコード、再試行時間などを記録し、質問文やAPIキー自体は追加ログに含めません。 |
| [CareerApplicationDefaults.php](../app/CareerApplicationDefaults.php) | 開発確認用の架空の応募先サンプルを配列で用意します。 |
| [GoogleCalendarService.php](../app/Services/GoogleCalendarService.php) | OAuthコード・アクセストークン交換、期限切れトークン更新、予定作成・更新、Googleカレンダー上の色ラベル準備を行います。 |
| [NeonActivityEstimateService.php](../app/Services/NeonActivityEstimateService.php) | Career AIがPostgreSQLへ接続した時間を記録し、Neon FreeプランのCU時間を推定します。請求上の実測値ではありません。 |
| [NeonUsageService.php](../app/Services/NeonUsageService.php) | 設定された管理者だけにDB容量・CU情報を返します。容量はPostgreSQLから、有料プランのCUは設定がある場合にNeon APIから取得します。 |
| [TrackNeonUsageEstimate.php](../app/Http/Middleware/TrackNeonUsageEstimate.php) | Webリクエスト後にNeon使用時間の記録処理を呼び出します。 |
| [AppServiceProvider.php](../app/Providers/AppServiceProvider.php) | アプリ起動時の共通設定を登録します。本番でURLをHTTPSにします。 |
| [TransferCareerAiAccount.php](../app/Console/Commands/TransferCareerAiAccount.php) | アカウントと所有データをローカルMySQLから移行先PostgreSQLへコピーするArtisanコマンドです。 |

### モデル

モデルはDBの表をPHPから扱い、データ同士の関係を定義します。castsはDBの値を日付・数値・配列などPHPで使いやすい型へ変換し、belongsToやhasManyなどのメソッドはテーブル間の関係を表します。

| ファイル | 内容 |
| --- | --- |
| [User.php](../app/Models/User.php) | ログイン利用者と、応募・企業プロフィール・招待との関連を表します。パスワードはハッシュ化し、Google更新トークンは暗号化して保存します。 |
| [CareerApplication.php](../app/Models/CareerApplication.php) | 本選考・インターンの応募先です。書類、面接準備、予定、企業プロフィールへつながります。 |
| [ApplicationDocument.php](../app/Models/ApplicationDocument.php) | 応募先に紐づくES・提出書類です。設問と回答をJSONデータとして扱います。 |
| [InterviewPreparation.php](../app/Models/InterviewPreparation.php) | 応募先ごとの面接準備メモや練習質問です。 |
| [ScheduleEvent.php](../app/Models/ScheduleEvent.php) | 面接・説明会・締切などの予定、完了状態、Google側イベントIDです。 |
| [CompanyProfile.php](../app/Models/CompanyProfile.php) | 利用者が入力した給与・休日・残業・福利厚生などです。応募先と結びつき、ランキングの元データになります。 |
| [CompanyResearch.php](../app/Models/CompanyResearch.php) | 旧AI企業研究機能のデータ形式です。現行の企業一覧・比較はCompanyProfileを使います。旧来の関連として残っています。 |
| [ResearchConversation.php](../app/Models/ResearchConversation.php) | 旧企業研究の会話を保存する形式です。現在web.phpに会話画面のルートはありません。 |
| [UserInvitation.php](../app/Models/UserInvitation.php) | 招待を作った利用者、有効期限、使用済み日時を表します。招待トークンはハッシュで保存します。 |
| [OwnedByCurrentUser.php](../app/Models/Concerns/OwnedByCurrentUser.php) | 複数のモデルで使う共通機能です。ログイン中の利用者のデータだけを検索し、新しいデータに利用者IDを設定します。 |

## URL・起動時のPHPファイル

| ファイル | 内容 |
| --- | --- |
| [routes/web.php](../routes/web.php) | ブラウザーのURLとHTTPメソッドをコントローラーへつなぎます。ログイン必須のルートはauthミドルウェアでまとめています。 |
| [routes/console.php](../routes/console.php) | Artisanコマンドの登録場所です。現在はLaravel標準のinspireコマンドを定義しています。 |
| [bootstrap/app.php](../bootstrap/app.php) | Laravelを起動し、ルート、ヘルスチェック、共通ミドルウェアを登録します。 |
| [bootstrap/providers.php](../bootstrap/providers.php) | 起動時に読み込むAppServiceProviderを登録します。 |
| [artisan](../artisan) | php artisan migrateなどのコマンドを実行するCLI入口です。 |
| [public/index.php](../public/index.php) | ブラウザーから届いたWebリクエストをLaravelへ渡す公開入口です。 |
## データベースの設計

Migration（マイグレーション）はDBの表・列を作る変更履歴です。新しい環境でartisan migrateを実行すると、ファイル名の順に適用されます。稼働済みのマイグレーションは書き換えず、新しい変更ファイルを追加するのが基本です。

| ファイル | 作る・変更する表 |
| --- | --- |
| [0001_01_01_000000_create_users_table.php](../database/migrations/0001_01_01_000000_create_users_table.php) | 利用者、パスワード再設定トークン、セッション。 |
| [0001_01_01_000001_create_cache_table.php](../database/migrations/0001_01_01_000001_create_cache_table.php) | キャッシュとロック。 |
| [0001_01_01_000002_create_jobs_table.php](../database/migrations/0001_01_01_000002_create_jobs_table.php) | キュー、一括ジョブ、失敗ジョブ用のLaravel標準表。Renderでは同期処理の設定です。 |
| [2026_11_02_155019_create_career_applications_table.php](../database/migrations/2026_11_02_155019_create_career_applications_table.php) | 応募区分、企業、コース、選考状況、メモ。 |
| [2026_11_02_162031_create_application_status_options_table.php](../database/migrations/2026_11_02_162031_create_application_status_options_table.php) | 利用者が追加する選考状況の選択肢。 |
| [2026_11_02_170007_create_application_documents_table.php](../database/migrations/2026_11_02_170007_create_application_documents_table.php) | ES本文と設問・回答。 |
| [2026_11_02_173821_create_interview_preparations_table.php](../database/migrations/2026_11_02_173821_create_interview_preparations_table.php) | 自己紹介、志望理由、強み・弱み、逆質問、練習質問。 |
| [2026_11_02_173822_create_company_research_table.php](../database/migrations/2026_11_02_173822_create_company_research_table.php) | 旧企業研究機能の業界、事業、競合、文化、出典など。 |
| [2026_11_02_173823_create_schedule_events_table.php](../database/migrations/2026_11_02_173823_create_schedule_events_table.php) | 予定の種類、タイトル、日時、場所、URL、メモ、完了状態。 |
| [2026_11_03_090000_add_company_and_status_to_schedule_events_table.php](../database/migrations/2026_11_03_090000_add_company_and_status_to_schedule_events_table.php) | 予定の企業名・選考区分・予定/確定状態。 |
| [2026_11_03_120000_add_meeting_format_to_schedule_events_table.php](../database/migrations/2026_11_03_120000_add_meeting_format_to_schedule_events_table.php) | オンライン/オフラインの開催形式。 |
| [2026_11_03_123252_add_user_ownership_and_research_conversations.php](../database/migrations/2026_11_03_123252_add_user_ownership_and_research_conversations.php) | 就活データの所有者、旧企業研究会話、招待情報など。 |
| [2026_11_03_131436_add_google_calendar_fields_to_users_and_schedule_events.php](../database/migrations/2026_11_03_131436_add_google_calendar_fields_to_users_and_schedule_events.php) | Google更新トークンと予定のGoogleイベントID。 |
| [2026_11_04_012654_make_user_ownership_required.php](../database/migrations/2026_11_04_012654_make_user_ownership_required.php) | 各就活データの所有者IDを必須にします。 |
| [2026_11_05_090000_create_neon_usage_estimates_table.php](../database/migrations/2026_11_05_090000_create_neon_usage_estimates_table.php) | Neon使用時間の月別推定記録。 |
| [2026_11_05_130000_add_result_date_to_career_applications.php](../database/migrations/2026_11_05_130000_add_result_date_to_career_applications.php) | 応募先の選考結果予定日。 |
| [2026_11_06_220000_create_company_profiles_table.php](../database/migrations/2026_11_06_220000_create_company_profiles_table.php) | 企業プロフィールと項目別比較データ。既存の応募先とプロフィールも関連付けます。 |

## 画面ファイル（Blade）

Bladeの二重波括弧は値の表示、@ifと@foreachは条件分岐・繰り返し、@includeは画面部品の再利用です。

| ファイル | 画面 |
| --- | --- |
| [welcome.blade.php](../resources/views/welcome.blade.php) | ログイン、デモログイン、バージョン表示。 |
| [auth/accept-invitation.blade.php](../resources/views/auth/accept-invitation.blade.php) | 招待リンクからのアカウント登録。 |
| [home.blade.php](../resources/views/home.blade.php) | 応募状況、近い予定・締切、ホーム用案内。 |
| [applications.blade.php](../resources/views/applications.blade.php) | 応募先一覧と追加・編集フォーム。一致・類似企業がある場合の保存前確認もここにあります。 |
| [documents/layout.blade.php](../resources/views/documents/layout.blade.php) | ログイン後画面の共通枠、ナビ、ヘッダー、アカウントメニュー、CSS/JS読み込み。 |
| [documents/index.blade.php](../resources/views/documents/index.blade.php) | ES・書類一覧と絞り込み。 |
| [documents/application.blade.php](../resources/views/documents/application.blade.php) | 企業別のES・書類一覧。 |
| [documents/create.blade.php](../resources/views/documents/create.blade.php) | ES・書類の新規登録。 |
| [documents/edit.blade.php](../resources/views/documents/edit.blade.php) | ES・書類の編集。共通フォームを読み込みます。 |
| [documents/form.blade.php](../resources/views/documents/form.blade.php) | ES本文と設問・回答の入力、AI整理ボタン、ローディング表示、送信制御。ページ固有のJavaScriptも含みます。 |
| [interviews/index.blade.php](../resources/views/interviews/index.blade.php) | 応募区分ごとの面接準備一覧。 |
| [interviews/show.blade.php](../resources/views/interviews/show.blade.php) | 企業ごとの面接準備の表示・編集。 |
| [research/index.blade.php](../resources/views/research/index.blade.php) | 登録企業、プロフィール、項目別ランキング。名前は旧来のresearchですが、現在は企業比較画面です。 |
| [research/edit.blade.php](../resources/views/research/edit.blade.php) | 企業の給与・休日・福利厚生などの編集。 |
| [schedule/index.blade.php](../resources/views/schedule/index.blade.php) | 月間カレンダーと予定一覧、予定操作。 |
| [schedule/form.blade.php](../resources/views/schedule/form.blade.php) | 予定の追加・編集。企業・種別の切替や時刻入力を補助するJavaScriptを含みます。 |
| [partials/navigation.blade.php](../resources/views/partials/navigation.blade.php) | PC/スマートフォン共通のメインナビゲーション。 |
| [partials/account-menu.blade.php](../resources/views/partials/account-menu.blade.php) | ログイン中の利用者情報とログアウト操作。 |
| [partials/application-row.blade.php](../resources/views/partials/application-row.blade.php) | 応募先1件の表示部品。 |
| [partials/demo-notice.blade.php](../resources/views/partials/demo-notice.blade.php) | 共有デモ利用時の注意表示。 |
| [partials/invitation-notice.blade.php](../resources/views/partials/invitation-notice.blade.php) | 招待リンク発行後の案内とコピー操作。 |
| [partials/neon-usage-card.blade.php](../resources/views/partials/neon-usage-card.blade.php) | 管理者向けNeon DB容量・CU推定カード。 |

## Factory・Seeder・テスト

Factoryはテスト用データの作り方、Seederは開発DBへ入れるサンプルデータを定義します。

| ファイル | 役割 |
| --- | --- |
| [ApplicationDocumentFactory.php](../database/factories/ApplicationDocumentFactory.php) | ES・書類テストデータ。 |
| [CareerApplicationFactory.php](../database/factories/CareerApplicationFactory.php) | 応募先テストデータ。 |
| [CompanyResearchFactory.php](../database/factories/CompanyResearchFactory.php) | 旧企業研究テストデータ。 |
| [InterviewPreparationFactory.php](../database/factories/InterviewPreparationFactory.php) | 面接準備テストデータ。 |
| [ResearchConversationFactory.php](../database/factories/ResearchConversationFactory.php) | 旧企業研究会話テストデータ。 |
| [ScheduleEventFactory.php](../database/factories/ScheduleEventFactory.php) | 予定テストデータ。 |
| [UserFactory.php](../database/factories/UserFactory.php) | 利用者テストデータ。 |
| [UserInvitationFactory.php](../database/factories/UserInvitationFactory.php) | 招待テストデータ。 |
| [CareerApplicationSeeder.php](../database/seeders/CareerApplicationSeeder.php) | CareerApplicationDefaultsの架空サンプルを登録します。 |
| [DatabaseSeeder.php](../database/seeders/DatabaseSeeder.php) | Artisanのdb:seedが最初に呼び出すSeederです。 |
| [tests/TestCase.php](../tests/TestCase.php) | Laravelテストの共通基底クラス。 |
| [tests/Feature/ExampleTest.php](../tests/Feature/ExampleTest.php) | HTTPリクエストを含む機能テストの例。 |
| [tests/Unit/ExampleTest.php](../tests/Unit/ExampleTest.php) | 小さな処理を単独で確認するテストの例。 |

## 設定・公開・フロントエンド

| ファイル | 役割 |
| --- | --- |
| [config/app.php](../config/app.php) | アプリ名、環境、タイムゾーンなどの基本設定。 |
| [config/auth.php](../config/auth.php) | ログイン認証の設定。 |
| [config/cache.php](../config/cache.php) | キャッシュの保存先。 |
| [config/database.php](../config/database.php) | SQLite、MySQL、PostgreSQLの接続設定。 |
| [config/filesystems.php](../config/filesystems.php) | ファイル保存先。 |
| [config/logging.php](../config/logging.php) | ログの保存先と形式。 |
| [config/mail.php](../config/mail.php) | メール送信設定。 |
| [config/queue.php](../config/queue.php) | バックグラウンド処理のキュー設定。 |
| [config/services.php](../config/services.php) | Gemini、Google Calendar、Neon、アプリ固有設定を環境変数から読み込みます。アプリバージョンもVERSIONから読みます。 |
| [config/session.php](../config/session.php) | ログイン状態などを保存するセッション設定。 |
| [render.yaml](../render.yaml) | Renderのサービス、ヘルスチェック、環境変数の名前と初期値。秘密値は書きません。 |
| [Dockerfile](../Dockerfile) | PHP 8.5.13とApacheの実行環境を作り、DB接続用PHP拡張、Composer依存、Vite生成物を組み込みます。 |
| [docker-entrypoint.sh](../docker-entrypoint.sh) | 起動時にDB接続を選び、設定キャッシュ、DBマイグレーション、Seeder、Apache起動を順に行います。 |
| [vite.config.js](../vite.config.js) | Laravel ViteプラグインとTailwind CSSのビルド設定。 |
| [resources/css/app.css](../resources/css/app.css) | Tailwind CSSの読み込みと共通フォント設定。 |
| [resources/js/app.js](../resources/js/app.js) | ViteのJavaScript入口です。画面ごとの操作コードは現在主にBlade内にあります。 |
| [public/index.php](../public/index.php) | WebリクエストをLaravelに渡す公開入口。 |
| [public/.htaccess](../public/.htaccess) | ApacheからLaravel公開入口へ処理を渡す設定。 |
| [public/robots.txt](../public/robots.txt) | 検索エンジン向けクロール設定。 |
| [public/favicon.ico](../public/favicon.ico) | ブラウザーのタブ用アイコン。 |
| [public/images/career-ai-meerkat.png](../public/images/career-ai-meerkat.png) | アプリ内で使うミーアキャット画像。 |
| [public/images/career-ai-meerkat-walk.png](../public/images/career-ai-meerkat-walk.png) | ログイン画面・ナビなどで使う歩くミーアキャット画像。 |

## ルートのMarkdownと開発補助ファイル

| ファイル | 内容 |
| --- | --- |
| [README.md](../README.md) | 利用者・開発者向け概要、起動方法、外部サービス、公開環境。 |
| [CHANGELOG.md](../CHANGELOG.md) | バージョンごとの変更点。 |
| [AGENTS.md](../AGENTS.md) | Codex等の開発エージェント向け作業指示・Laravelルール。 |
| [CLAUDE.md](../CLAUDE.md) | Claude Code向けの作業案内。 |
| [composer.json](../composer.json) | PHPパッケージとComposerコマンド。 |
| [composer.lock](../composer.lock) | PHPパッケージの正確なバージョン固定。通常は直接編集しません。 |
| [package.json](../package.json) | npmパッケージとViteのbuild/devコマンド。 |
| [package-lock.json](../package-lock.json) | npmパッケージのバージョン固定。通常は直接編集しません。 |
| [phpunit.xml](../phpunit.xml) | PHPUnitのテスト環境設定。 |
| [boost.json](../boost.json) | Laravel Boost開発補助の設定。 |
| [.env.example](../.env.example) | ローカル設定のひな形。秘密情報を入れる実際の.envはGitに登録しません。 |
| [.gitignore](../.gitignore) | Gitで追跡しない設定・生成物。 |
| [.dockerignore](../.dockerignore) | Dockerイメージに含めないファイル。 |
| [.editorconfig](../.editorconfig) | エディター間の改行やインデントを揃えます。 |
| [.gitattributes](../.gitattributes) | Git上での改行などファイル属性を揃えます。 |
| [.npmrc](../.npmrc) | npmのプロジェクト設定。 |
| [VERSION](../VERSION) | アプリのバージョン番号。 |
| [database/.gitignore](../database/.gitignore) / [bootstrap/cache/.gitignore](../bootstrap/cache/.gitignore) | ローカルDBや生成キャッシュをGitに登録しない指定。 |
| [storage/app/.gitignore](../storage/app/.gitignore)、[storage/app/private/.gitignore](../storage/app/private/.gitignore)、[storage/app/public/.gitignore](../storage/app/public/.gitignore) | アップロード・公開ファイル用の空ディレクトリをGitに残します。 |
| [storage/framework/.gitignore](../storage/framework/.gitignore)、[storage/framework/cache/.gitignore](../storage/framework/cache/.gitignore)、[storage/framework/cache/data/.gitignore](../storage/framework/cache/data/.gitignore)、[storage/framework/cache/locks/.gitignore](../storage/framework/cache/locks/.gitignore) | 実行時キャッシュ用フォルダーをGitに残します。 |
| [storage/framework/sessions/.gitignore](../storage/framework/sessions/.gitignore)、[storage/framework/testing/.gitignore](../storage/framework/testing/.gitignore)、[storage/framework/views/.gitignore](../storage/framework/views/.gitignore)、[storage/logs/.gitignore](../storage/logs/.gitignore) | セッション・テスト・生成ビュー・ログ用フォルダーをGitに残します。 |

## .agents/skills のMarkdown

以下はWebアプリの画面ではなく、Codexが開発時に読む手順書・確認表です。

| ファイル | 何の資料か |
| --- | --- |
| [.agents/skills/deploying-to-cloud/SKILL.md](../.agents/skills/deploying-to-cloud/SKILL.md) | クラウドデプロイ手順の入口。 |
| [.agents/skills/deploying-to-cloud/reference/checklists.md](../.agents/skills/deploying-to-cloud/reference/checklists.md) | クラウド公開前後のチェックリスト。 |
| [.agents/skills/infer-conventions/SKILL.md](../.agents/skills/infer-conventions/SKILL.md) | 既存コードの設計・命名を調べる手順。 |
| [.agents/skills/infer-conventions/references/checklist.md](../.agents/skills/infer-conventions/references/checklist.md) | 既存コードの調査チェック表。 |
| [.agents/skills/laravel-best-practices/SKILL.md](../.agents/skills/laravel-best-practices/SKILL.md) | Laravelルールの入口。 |
| [.agents/skills/laravel-best-practices/rules/advanced-queries.md](../.agents/skills/laravel-best-practices/rules/advanced-queries.md) | 複雑なDB検索。 |
| [.agents/skills/laravel-best-practices/rules/architecture.md](../.agents/skills/laravel-best-practices/rules/architecture.md) | Laravelの構成と責任分担。 |
| [.agents/skills/laravel-best-practices/rules/blade-views.md](../.agents/skills/laravel-best-practices/rules/blade-views.md) | Blade画面。 |
| [.agents/skills/laravel-best-practices/rules/caching.md](../.agents/skills/laravel-best-practices/rules/caching.md) | キャッシュ。 |
| [.agents/skills/laravel-best-practices/rules/collections.md](../.agents/skills/laravel-best-practices/rules/collections.md) | Collectionによるデータ処理。 |
| [.agents/skills/laravel-best-practices/rules/config.md](../.agents/skills/laravel-best-practices/rules/config.md) | 設定と環境変数。 |
| [.agents/skills/laravel-best-practices/rules/db-performance.md](../.agents/skills/laravel-best-practices/rules/db-performance.md) | DBアクセスの性能。 |
| [.agents/skills/laravel-best-practices/rules/eloquent.md](../.agents/skills/laravel-best-practices/rules/eloquent.md) | Eloquentモデルと関連。 |
| [.agents/skills/laravel-best-practices/rules/error-handling.md](../.agents/skills/laravel-best-practices/rules/error-handling.md) | エラー処理とログ。 |
| [.agents/skills/laravel-best-practices/rules/events-notifications.md](../.agents/skills/laravel-best-practices/rules/events-notifications.md) | イベントと通知。 |
| [.agents/skills/laravel-best-practices/rules/http-client.md](../.agents/skills/laravel-best-practices/rules/http-client.md) | 外部API通信。 |
| [.agents/skills/laravel-best-practices/rules/mail.md](../.agents/skills/laravel-best-practices/rules/mail.md) | メール送信。 |
| [.agents/skills/laravel-best-practices/rules/migrations.md](../.agents/skills/laravel-best-practices/rules/migrations.md) | DB構造変更。 |
| [.agents/skills/laravel-best-practices/rules/queue-jobs.md](../.agents/skills/laravel-best-practices/rules/queue-jobs.md) | キューとジョブ。 |
| [.agents/skills/laravel-best-practices/rules/routing.md](../.agents/skills/laravel-best-practices/rules/routing.md) | ルート定義。 |
| [.agents/skills/laravel-best-practices/rules/scheduling.md](../.agents/skills/laravel-best-practices/rules/scheduling.md) | 定期実行。 |
| [.agents/skills/laravel-best-practices/rules/security.md](../.agents/skills/laravel-best-practices/rules/security.md) | 認証、入力、秘密情報の安全な扱い。 |
| [.agents/skills/laravel-best-practices/rules/style.md](../.agents/skills/laravel-best-practices/rules/style.md) | PHP/Laravelの書き方。 |
| [.agents/skills/laravel-best-practices/rules/validation.md](../.agents/skills/laravel-best-practices/rules/validation.md) | 入力値の検証。 |
| [.agents/skills/tailwindcss-development/SKILL.md](../.agents/skills/tailwindcss-development/SKILL.md) | Tailwind CSSの開発手順。 |
| [.agents/skills/testing-best-practices/SKILL.md](../.agents/skills/testing-best-practices/SKILL.md) | テストの作成・確認ルールの入口。 |
| [.agents/skills/testing-best-practices/rules/assertions.md](../.agents/skills/testing-best-practices/rules/assertions.md) | テスト結果の表明方法。 |
| [.agents/skills/testing-best-practices/rules/endpoint-tests.md](../.agents/skills/testing-best-practices/rules/endpoint-tests.md) | URL・HTTPのテスト。 |
| [.agents/skills/testing-best-practices/rules/finding-features.md](../.agents/skills/testing-best-practices/rules/finding-features.md) | テスト対象の探し方。 |
| [.agents/skills/testing-best-practices/rules/isolation.md](../.agents/skills/testing-best-practices/rules/isolation.md) | テスト間の状態分離。 |
| [.agents/skills/testing-best-practices/rules/naming.md](../.agents/skills/testing-best-practices/rules/naming.md) | テスト名と構成。 |
| [.agents/skills/testing-best-practices/rules/performance.md](../.agents/skills/testing-best-practices/rules/performance.md) | テスト速度。 |
| [.agents/skills/testing-best-practices/rules/review.md](../.agents/skills/testing-best-practices/rules/review.md) | テストコードの見直し方。 |
| [.agents/skills/testing-best-practices/rules/security.md](../.agents/skills/testing-best-practices/rules/security.md) | セキュリティ関連のテスト観点。 |
| [.agents/skills/testing-best-practices/rules/test-data.md](../.agents/skills/testing-best-practices/rules/test-data.md) | テストデータの用意。 |

## ファイルを探すコツ

- URLが分かるときはroutes/web.phpで担当処理を探します。
- 画面の文章や配置は対応するresources/viewsのBladeを探します。
- 保存項目はコントローラーの入力検証、モデル、Migrationを一緒に確認します。
- 利用者ごとのデータ分離はOwnedByCurrentUserと認証済み利用者の条件を確認します。
- 外部APIの変更は画面ControllerとServiceまたはParserの両方を確認します。

## 案内対象に含めていないもの

vendor/とnode_modules/はComposer/npmが取得する外部ライブラリです。storage/framework/やbootstrap/cache/の実行時ファイルはLaravelの生成物です。実際の.envやローカルDBファイルは秘密情報・個人データを含むためGitで追跡しません。通常の機能変更では直接編集しません。
