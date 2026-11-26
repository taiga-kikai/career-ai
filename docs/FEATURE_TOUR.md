# Career AI 機能紹介ガイド

Career AIの機能を初めての人に順番よく紹介し、説明中にコードの場所も示すための資料です。紹介するときは、まず利用者の困りごとを話し、画面を見せてから、対応するファイルを案内すると流れが伝わりやすくなります。

各ファイルの一覧は[ファイル案内](CODE_MAP.md)、アプリの起動と設定は[README](../README.md)を参照してください。

## 5〜10分で紹介する順番

### 1. 解決したいことと全体像

応募先が増えると、選考状況、ES、面接準備、締切が別々の場所に散らばります。Career AIはこれらを一か所で管理する個人開発のWebアプリです。ホームでは、応募状況や近い予定・締切をまとめて確認できます。

**画面で見せる場所:** `/home`

**コード:** [HomeController.php](../app/Http/Controllers/HomeController.php) が表示用データを集め、[home.blade.php](../resources/views/home.blade.php) が画面を作ります。

### 2. 企業・応募の管理

本選考やインターンを分け、企業名、選考段階、メモなどを記録できます。同名・似た企業名が登録済みの場合は、保存前に確認を出します。予定も一緒に登録した場合は応募先と予定をまとめて保存します。

**画面で見せる場所:** `/applications`

**コード:** [applications.blade.php](../resources/views/applications.blade.php) に一覧・入力欄・企業名の重複確認があります。[ApplicationManagementPreviewController.php](../app/Http/Controllers/ApplicationManagementPreviewController.php) が入力検証、DB保存、企業プロフィールとの関連付け、予定の同時作成を行います。[routes/web.php](../routes/web.php) で `/applications` の各HTTPメソッドと処理先を確認できます。保存をまとめるトランザクションもコントローラーにあります。

### 3. ES・書類の保存と設問整理

ESの原文と設問・回答を応募先ごとに保存できます。Geminiを使う場合は、長い貼り付け文章から設問と回答の組をJSONで抽出し、利用者が確認・編集してから保存します。Geminiを設定していない場合やAPI処理に失敗した場合は、ローカルの簡易整理へ切り替えます。

**画面で見せる場所:** `/documents`

**コード:** [documents/form.blade.php](../resources/views/documents/form.blade.php) のボタンとJavaScript → [routes/web.php](../routes/web.php) の `documents.parse` → [ApplicationDocumentController.php](../app/Http/Controllers/ApplicationDocumentController.php) の `parse()` → [EsQuestionParser.php](../app/EsQuestionParser.php) の `parse()` が処理順です。保存は同コントローラーの `store()` / `update()` が担当します。

Geminiの詳しい処理は[ES設問整理: Gemini API](#es設問整理-gemini-api)を参照してください。

### 4. 面接準備

自己紹介、志望動機、学生時代の経験、強み・弱み、逆質問、練習質問を応募先ごとに保存できます。保存済みの内容を見直し、編集できます。

**画面で見せる場所:** `/interviews`

**コード:** [InterviewPreparationController.php](../app/Http/Controllers/InterviewPreparationController.php)、[InterviewPreparation.php](../app/Models/InterviewPreparation.php)、[interviews画面](../resources/views/interviews/)、[テーブル作成Migration](../database/migrations/2026_11_02_173821_create_interview_preparations_table.php)。

### 5. 予定とカレンダー

面接・説明会・提出締切などを月間カレンダーで管理します。予定の開始日時を入力すると終了日時を1時間後に補助入力し、締切は終了時刻のない予定として扱います。Googleカレンダー連携を設定すると、OAuthで本人の許可を得て予定を作成・更新できます。

**画面で見せる場所:** `/schedule`

**コード:** [ScheduleController.php](../app/Http/Controllers/ScheduleController.php) と [schedule/form.blade.php](../resources/views/schedule/form.blade.php) が予定の入力・表示を担当します。[予定テーブルのMigration](../database/migrations/2026_11_02_173823_create_schedule_events_table.php) も確認できます。Google連携は [GoogleCalendarController.php](../app/Http/Controllers/GoogleCalendarController.php) → [GoogleCalendarService.php](../app/Services/GoogleCalendarService.php) の順に追えます。

### 6. 登録企業の情報・項目別比較

応募先の中から企業情報を入力した会社を一覧表示し、初任給・平均年収・残業時間・年間休日など、項目ごとに比較します。値が未入力の会社はその項目の順位から外れます。企業情報の自動検索や総合ランキングは行いません。

**画面で見せる場所:** `/research`

**コード:** [CompanyDirectoryController.php](../app/Http/Controllers/CompanyDirectoryController.php)、[CompanyProfile.php](../app/Models/CompanyProfile.php)、[research画面](../resources/views/research/)、[テーブル作成Migration](../database/migrations/2026_11_06_220000_create_company_profiles_table.php)。

### 7. アカウントとデータ分離

招待リンクでアカウントを作り、メールアドレスとPINでログインします。就活データのモデルには現在の利用者だけを検索対象にする共通スコープがあり、作成時には所有者IDを設定します。

**コード:** [routes/web.php](../routes/web.php) の `auth` グループ → [AuthController.php](../app/Http/Controllers/AuthController.php) / [InvitationController.php](../app/Http/Controllers/InvitationController.php) → [OwnedByCurrentUser.php](../app/Models/Concerns/OwnedByCurrentUser.php)。

## ES設問整理: Gemini API

Gemini連携を説明するときは、次の順番でコードを開くと、入力から結果までを一続きで示せます。

**現在の機能はESの添削・評価ではなく、設問と回答の整理です。** 整理ボタンを押すと、貼り付け欄の本文全体を一つのリクエストでGeminiに送り、設問ごとに分けた結果を受け取ります。送信前にプログラム側で設問単位へ分割しているわけではありません。

1. **画面:** `resources/views/documents/form.blade.php` の整理ボタンが本文を `/documents/parse` に送ります。処理中表示と二重送信防止も画面側にあります。
2. **URL:** `routes/web.php` の `POST /documents/parse` が受け取り先を `ApplicationDocumentController::parse` に結び付けます。
3. **入力確認:** `ApplicationDocumentController::parse()` が `raw_text` の存在・文字数を検証してからParserを呼びます。
4. **Geminiの有無:** `EsQuestionParser::parse()` が設定済みかを確認します。APIキーがない場合、外部送信せず `splitLocally()` で整理します。
5. **API呼び出し:** `parseWithGemini()` が `config/services.php` のモデル名とAPIキーを読み、質問・回答をJSONで返すよう指示してGoogleのInteractions APIへ送信します。リクエストには `store: false` を指定しています。
6. **結果の読み取り:** 応答のmodel outputからテキストを集め、JSONを読み、空の質問を除いて画面用の配列に整えます。
7. **エラー時:** 接続失敗、HTTPエラー、応答JSONの不備などは例外として扱います。ログにはモデル名、HTTP状態、Googleのエラーコード・状態・メッセージ、再試行情報を記録します。アプリはES本文やAPIキーを独立したログ項目として渡しません。APIキーはGoogleのエラー文に含まれた場合にも置換します。画面へは `splitLocally()` の結果を返し、利用者が内容を確認・編集できるようにします。
8. **保存:** 画面で内容を確認した後、通常の保存リクエストが `ApplicationDocumentController::store()` または `update()` に届き、入力検証後に `ApplicationDocument` 経由でDBへ保存されます。

**設定場所:** `config/services.php` は `GEMINI_API_KEY` と `GEMINI_MODEL` を読む設定ファイルです。APIキー本体は `.env` またはRender等の環境変数に置き、ソースコードやGitHubへ書き込みません。

### 設問の分け方を決めている場所

| 役割 | ファイル・処理 | 内容 |
| --- | --- | --- |
| 全文を画面から送信 | [documents/form.blade.php](../resources/views/documents/form.blade.php) | `raw_text`欄の文章全体をJSON本文に入れて`POST /documents/parse`へ送る。 |
| 入力上限とParser呼び出し | [ApplicationDocumentController.php](../app/Http/Controllers/ApplicationDocumentController.php) の `parse()` | `raw_text`を必須文字列、最大50,000文字として検証し、その全文をParserへ渡す。 |
| Geminiへ渡す指示 | [EsQuestionParser.php](../app/EsQuestionParser.php) の `parseWithGemini()` | 本文の設問・回答を順番どおり抽出し、創作・要約をせず、判別できない箇所は原文を保つよう指示する。 |
| 返却JSONの形 | 同メソッドの `$schema` | `questions`配列の各要素に、文字列の`question`と`answer`を必須項目として指定する。 |
| Geminiを使えない場合 | 同ファイルの `splitLocally()` | 質問番号や依頼文の表現を正規表現で探す簡易ルール。Geminiへは送信しない。 |

指示文は`EsQuestionParser.php`内の`$prompt`、JSON形式は`$schema`で定めています。Geminiが本文の区切りを誤る場合もあるため、結果は画面で確認・修正してから保存する仕組みです。

> Geminiの補助整理は結果の正確さを保証しません。質問と回答が混ざった場合に修正できるよう、保存前の確認・編集を前提にしています。

## Googleカレンダー連携

1. `GoogleCalendarController::connect()` が利用者と機能設定を確認し、OAuthの `state` をセッションに保存します。
2. `GoogleCalendarService::authorizationUrl()` がGoogleの許可画面へ移動するURLを作ります。
3. Googleから戻る `callback()` がstateを照合し、認可コードをアクセストークンへ交換します。
4. 更新トークンは `User` モデルの暗号化キャストを通じてDBに保存します。
5. 予定の登録・更新では `syncEvent()` が一時アクセストークンを取得し、Google Calendar APIへ予定を送ります。Google側のイベントIDをCareer AIにも保存し、次回は同じ予定を更新します。

確認するファイル: [routes/web.php](../routes/web.php)、[GoogleCalendarController.php](../app/Http/Controllers/GoogleCalendarController.php)、[GoogleCalendarService.php](../app/Services/GoogleCalendarService.php)、[User.php](../app/Models/User.php)、[ScheduleEvent.php](../app/Models/ScheduleEvent.php)。

## コードを読むときの共通ルート

```text
routes/web.php
  → app/Http/Controllers/ の該当コントローラー
  → app/EsQuestionParser.php または app/Services/ の処理
  → app/Models/ のDBモデル
  → database/migrations/ のテーブル定義
  → resources/views/ のBlade画面
```

- **Route:** URLとHTTPメソッドを、処理先へ結び付けます。
- **Controller:** 入力確認、処理の呼び出し、画面またはJSON応答を担当します。
- **Service / Parser:** 外部APIや、複数画面で使うまとまった処理を担当します。
- **Model:** DBの表をPHPから読み書きし、表同士の関係を示します。
- **Migration:** DBに作る表や列、変更履歴をコードで定義します。
- **Blade:** HTMLを組み立て、画面固有の操作を表示します。

PHPファイルのクラス・メソッドには日本語のPHPDocを加え、処理を読む入口をソース内にも置いています。コメントはコードの一行一行を言い換えるのではなく、役割や安全上の理由が初見で分かりにくい部分を説明します。
