# Changelog

## v1.09

- ローカルMySQLからNeonへ全アカウントと紐づく就活データを一括移行できるように改善
- 同一メールアドレスによる重複移行を防止し、移行先DB内のトランザクションで保護
- ログインセッションとGoogle Calendar認証情報は移行対象外として明示

## v1.08

- ホームに本人専用のNeon容量・CU利用カードを追加
- 保存量はPostgreSQLから取得し、CU実績は利用可能な有料プランのNeon APIから取得
- DB容量やCU上限の変更はNeon管理画面を開く方式に統一

## v1.07

- ローカルMySQLからPostgreSQLへ本人アカウントと紐づく就活データを移すコマンドを追加
- Googleカレンダー認証情報は暗号鍵の違いを考慮して移行対象から除外

## v1.06

- Render起動時に外部PostgreSQLの`DB_URL`があれば接続するよう対応
- DockerイメージにPostgreSQL用PDOドライバーを追加
- 外部DBを使わない既存RenderデモはSQLiteのまま動作

## v1.05

- 公開環境で生成する画面・フォームのURLをHTTPSに統一
- 公開環境のセッションCookieをHTTPS専用に設定

## v1.04

- READMEにRender公開デモのURLを追加

## v1.03

- Renderが自動生成するBase64形式の`APP_KEY`をLaravel向けに正規化
- `APP_KEY`未設定時に起動ログへ明確なエラーを出す

## v1.02

- RenderのヘルスチェックをLaravel標準の`/up`へ変更
- RenderでLaravelのエラーをRuntime Logsへ出力

## v1.01

- SQLiteで途中失敗したスケジュール移行を安全に再実行できるよう修正
- SQLiteで動作しないJOIN更新を、DB共通のレコード更新に変更

## v1.00

- 初回リリース
- アカウントごとに分離した就活データ管理
- スマートフォン向け画面とモバイル用スケジュール一覧
- 無料Renderデモ向けDocker/Blueprint設定
- 公開デモに共有データと一時保存の注意を表示
