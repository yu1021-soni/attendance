<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceApprovalTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 承認待ちの修正申請が全て表示されている
    public function test_pending_corrections_are_displayed() {

        // 1. 管理者ユーザーにログインをする

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く

        // 全ユーザーの未承認の修正申請が表示される
    }

    // 承認済みの修正申請が全て表示されている
    public function test_approved_corrections_are_displayed() {

        // 1. 管理者ユーザーにログインをする

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く

        // 全ユーザーの承認済みの修正申請が表示される
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_correction_detail_displays_correct_information() {

        // 1. 管理者ユーザーにログインをする

        // 2. 修正申請の詳細画面を開く

        // 申請内容が正しく表示されている
    }

    // 修正申請の承認処理が正しく行われる
    public function test_correction_approval_updates_database() {

        // 1. 管理者ユーザーにログインをする

        // 2. 修正申請の詳細画面で「承認」ボタンを押す

        // 修正申請が承認され、勤怠情報が更新される
    }
}
