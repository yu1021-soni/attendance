# attendance

## プロジェクト概要
「勤怠管理アプリ」は、企業向けに開発された独自の勤怠管理システムです。

社員の出勤・退勤・休憩時間を記録し、管理者が勤怠データを一元的に把握できるWebアプリケーションです。

ユーザーはブラウザ上で打刻、履歴確認、修正申請などを簡単に行うことができます。

## 環境構築手順

1. リポジトリをクローン

    `git clone https://github.com/yu1021-soni/attendance.git`

2. Docker起動

3. プロジェクト直下で、以下のコマンドを実行する

    `make init`

## メール認証テスト

   Mailhogを利用。

   ブラウザで以下にアクセス。

   http://localhost:8025

   `php artisan queue:work`

## テスト実行
1. テスト専用の環境設定ファイル .env.testing を用意
2. .env.testingに以下を記述

   テスト用アプリケーションキーの作成

   `php artisan key:generate --env=testing`
   ```
   APP_ENV=testing
   APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

   APP_DEBUG=true

   DB_CONNECTION=sqlite
   DB_DATABASE=:memory:

   CACHE_DRIVER=file
   SESSION_DRIVER=file
   QUEUE_CONNECTION=sync
   ```

3. テスト実行

    `php artisan test`

## 管理者ユーザー情報
Seeder により以下のユーザーが作成されます。

いずれも **パスワードは `password`** です。

- 管理者 / admin@example.com  

## 一般ユーザー情報
Seeder により以下のユーザーが作成されます。

いずれも **パスワードは `password`** です。

- 佐藤 太郎 / sato.taro@example.com  
- 鈴木 花子 / suzuki.hanako@example.com  
- 高橋 健 / takahashi.ken@example.com  
- 田中 美咲 / tanaka.misaki@example.com  
- 伊藤 翔 / ito.sho@example.com.  

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
![alt text](https://file%2B.vscode-resource.vscode-cdn.net/Users/yu/coachtech/attendance/attendance.svg?version%3D1765653679356)

## URL
- アプリケーション: http://localhost
- phpMyAdmin: http://localhost:8080
- Mailhog: http://localhost:8025
