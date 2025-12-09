<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminGetUserInfoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_view_all_general_users_with_name_and_email() {

        // 1. 管理者でログインする

        // 2. スタッフ一覧ページを開く

        // 全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_attendance_records_are_displayed_correctly() {

        // 1. 管理者ユーザーでログインする

        // 2. 選択したユーザーの勤怠一覧ページを開く

        // 勤怠情報が正確に表示される
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_button_displays_previous_month_records() {

        // 1. 管理者ユーザーにログインをする

        // 2. 勤怠一覧ページを開く

        // 3. 「前月」ボタンを押す

        // 前月の情報が表示されている
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_next_month_button_displays_next_month_records() {

        // 1. 管理者ユーザーにログインをする

        // 2. 勤怠一覧ページを開く

        // 3. 「翌月」ボタンを押す

        // 翌月の情報が表示されている
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_detail_button_navigates_to_attendance_detail_page() {

        // 1. 管理者ユーザーにログインをする

        // 2. 勤怠一覧ページを開く

        // 3. 「詳細」ボタンを押下する 

        // その日の勤怠詳細画面に遷移する
    }
}
