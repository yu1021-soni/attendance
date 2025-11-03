# attendance

## プロジェクト概要
「勤怠管理アプリ」は、企業向けに開発された独自の勤怠管理システムです。

社員の出勤・退勤・休憩時間を記録し、管理者が勤怠データを一元的に把握できるWebアプリケーションです。

ユーザーはブラウザ上で打刻、履歴確認、修正申請などを簡単に行うことができます。

## 環境構築手順

1. リポジトリをクローン

    `git clone　https://github.com/yu1021-soni/attendance.git`

2. Docker起動

3. プロジェクト直下で、以下のコマンドを実行する

    `make init`

## メール認証テスト

   Mailhogを利用。

   ブラウザで以下にアクセス。

   http://localhost:8025

   `php artisan queue:work`

## テスト実行

## 使用技術

・基盤
- PHP 8.1.33
- Laravel 8.83.29
- Mysql 8.0.26
- nginx 1.21.1
- Composer 2.8.12
- Docker / Docker Compose

・主要パッケージ
- Laravel Fortify
- PHPUnit

・開発用ツール
- phpMyAdmin
- Mailhog

## ER図

## URL
- アプリケーション: http://localhost
- phpMyAdmin: http://localhost:8080
- Mailhog: http://localhost:8025
