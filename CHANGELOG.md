# Changelog

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
