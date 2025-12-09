<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Correction;

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

        // 勤怠日
        $attendanceDate = Carbon::create(2025, 12, 3);

        // ユーザーを2人
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $attendanceDate,
            'work_start' => $attendanceDate->copy()->setTime(9, 0),
            'work_end' => $attendanceDate->copy()->setTime(18, 0),
            'status' => 3,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $attendanceDate,
            'work_start' => $attendanceDate->copy()->setTime(9, 0),
            'work_end' => $attendanceDate->copy()->setTime(18, 0),
            'status' => 3,
        ]);

        // 承認待ちの修正申請を2件作成
        $pending1 = Correction::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => $attendanceDate,
            'comment' => 'ユーザー1の修正',
            'status' => 1,
        ]);

        $pending2 = Correction::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'date' => $attendanceDate,
            'comment' => 'ユーザー2の修正',
            'status' => 1,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 修正申請一覧ページを開き、承認待ちのタブを開く
        $response = $this->get(route('approval.index'));
        $response->assertOk();

        // 全ユーザーの未承認の修正申請が表示される
        $response->assertSeeText($user1->name);
        $response->assertSeeText($pending1->comment);
        $response->assertSeeText($user2->name);
        $response->assertSeeText($pending2->comment);
    }

    // 承認済みの修正申請が全て表示されている
    public function test_approved_corrections_are_displayed() {

        // 勤怠日
        $attendanceDate = Carbon::create(2025, 12, 3);

        // ユーザーを2人
        $user1 = User::factory()->create(['name' => 'ユーザー1']);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $attendanceDate,
            'work_start' => $attendanceDate->copy()->setTime(9, 0),
            'work_end' => $attendanceDate->copy()->setTime(18, 0),
            'status' => 3,
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $attendanceDate,
            'work_start' => $attendanceDate->copy()->setTime(9, 0),
            'work_end' => $attendanceDate->copy()->setTime(18, 0),
            'status' => 3,
        ]);

        // 承認済みの修正申請を2件作成
        $approval1 = Correction::create([
            'user_id' => $user1->id,
            'attendance_id' => $attendance1->id,
            'date' => $attendanceDate,
            'comment' => 'ユーザー1の承認済',
            'status' => 2,
        ]);

        $approval2 = Correction::create([
            'user_id' => $user2->id,
            'attendance_id' => $attendance2->id,
            'date' => $attendanceDate,
            'comment' => 'ユーザー2の承認済',
            'status'  => 2,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);


        // 2. 修正申請一覧ページを開き、承認済みのタブを開く
        $response = $this->get(route('approval.index', ['tab' => 'approved']));
        $response->assertOk();

        // 全ユーザーの承認済みの修正申請が表示される
        $response->assertSeeText($user1->name);
        $response->assertSeeText($approval1->comment);
        $response->assertSeeText($user2->name);
        $response->assertSeeText($approval2->comment);
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_correction_detail_displays_correct_information() {

        // 勤怠日
        $attendanceDate = Carbon::create(2025, 12, 3);

        $user = User::factory()->create([
            'email' => 'test@example.com', 'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id, 'date' => $attendanceDate, 'work_start' => $attendanceDate->copy()->setTime(9, 0), 'work_end' => $attendanceDate->copy()->setTime(18, 0), 'status' => 3,
        ]);
        $attendance->rests()->create([
            'rest_start' => $attendanceDate->copy()->setTime(12, 0), 'rest_end' => $attendanceDate->copy()->setTime(13, 0),
        ]);

        $correction = Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendanceDate,
            'comment' => 'テストコメント',
            'status' => 1,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 修正申請の詳細画面を開く
        $response = $this->get(route('approval.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 申請内容が正しく表示されている
        $response->assertSeeText($user->name);
        $response->assertSeeText($attendanceDate->format('Y') . '年');
        $response->assertSeeText($attendanceDate->format('n月j日'));
        $response->assertSeeText($correction->comment);
    }

    // 修正申請の承認処理が正しく行われる
    public function test_correction_approval_updates_database() {

        // 勤怠日
        $attendanceDate = Carbon::create(2025, 12, 3);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $attendanceDate,
            'work_start' => $attendanceDate->copy()->setTime(9, 0),
            'work_end' => $attendanceDate->copy()->setTime(18, 0),
            'status' => 3,
        ]);
        $attendance->rests()->create([
            'rest_start' => $attendanceDate->copy()->setTime(12, 0),
            'rest_end' => $attendanceDate->copy()->setTime(13, 0),
        ]);

        $correction = Correction::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'date' => $attendanceDate,
            'comment' => 'テストコメント',
            'status' => 1,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 修正申請の詳細画面で「承認」ボタンを押す
        $response = $this->post(route('admin.approval', ['id' => $attendance->id]));
        $response->assertStatus(302);

        // 修正申請が承認され、勤怠情報が更新される
        $this->assertDatabaseHas('corrections', [
            'id' => $correction->id,
            'status' =>2,
            'approver_id' => $admin->id,
        ]);
        
        $this->assertDatabaseHas('attendances', [
            'id'      => $attendance->id,
            'comment' => 'テストコメント',
        ]);

        $detailResponse = $this->get(route('approval.show', ['id' => $attendance->id]));
        $detailResponse->assertOk();
        $detailResponse->assertSeeText('承認済み');
    }
}
