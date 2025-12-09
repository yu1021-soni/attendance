<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_daily_attendance_page_displays_all_users_records()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        // 1. 管理者ユーザーにログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 一般ユーザーを2人作成
        $user1 = User::factory()->create([
            'name' => 'ユーザー1',
            'role' => 0,
        ]);

        $user2 = User::factory()->create([
            'name' => 'ユーザー2',
            'role' => 0,
        ]);

        // その日の勤怠を2人分登録
        $attendance1 = Attendance::create([
            'user_id'    => $user1->id,
            'date'       => Carbon::today(),
            'work_start' => Carbon::today()->copy()->setTime(9, 0),
            'work_end'   => Carbon::today()->copy()->setTime(18, 0),
            'comment'    => null,
            'status'     => 3,
        ]);

        $attendance2 = Attendance::create([
            'user_id'    => $user2->id,
            'date'       => Carbon::today(),
            'work_start' => Carbon::today()->copy()->setTime(10, 0),
            'work_end'   => Carbon::today()->copy()->setTime(19, 0),
            'comment'    => null,
            'status'     => 3,
        ]);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();

        // その日の全ユーザーの勤怠情報が正確な値になっている
        $response->assertSee('ユーザー1');
        $response->assertSee('ユーザー2');

        $response->assertSee($attendance1->work_start->format('H:i'));
        $response->assertSee($attendance1->work_end->format('H:i'));
        $response->assertSee($attendance2->work_start->format('H:i'));
        $response->assertSee($attendance2->work_end->format('H:i'));
    }

    // 遷移した際に現在の日付が表示される
    public function test_daily_attendance_page_shows_today_when_accessed()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 2, 10, 9, 0, 0));

        // 1. 管理者ユーザーにログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠一覧画面を開く
        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();

        // 勤怠一覧画面にその日の日付が表示されている
        $todayString = Carbon::today()->format('Y/m/d');

        $response->assertSee($todayString);
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_daily_attendance_page_prev_button_shows_previous_day_records()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 3, 10, 9, 0, 0));

        // 1. 管理者ユーザーにログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);
        $this->actingAs($admin);

        // 一般ユーザーを1人作成
        $user = User::factory()->create([
            'name' => '前日ユーザー',
            'role' => 0,
        ]);

        // 「今日」となる基準日
        $baseDate = Carbon::today(); 
        $prevDate = $baseDate->copy()->subDay();

        // 前日（3/9）の勤怠
        $prevAttendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => $prevDate,
            'work_start' => $prevDate->copy()->setTime(9, 0),
            'work_end'   => $prevDate->copy()->setTime(18, 0),
            'comment'    => null,
            'status'     => 3,
        ]);

        // 2. 勤怠一覧画面を開く
        // ３. 「前日」ボタンを押す
        $response = $this->get(route('admin.dashboard', [
            'year' => $baseDate->year,
            'month' => $baseDate->month,
            'day' => $baseDate->day,
            'move' => 'prev',
        ]));

        $response->assertOk();

        // 前日の日付の勤怠情報が表示される
        $response->assertSee($prevDate->format('Y/m/d'));
        $response->assertSee($prevAttendance->work_start->format('H:i'));
        $response->assertSee($prevAttendance->work_end->format('H:i'));

        // 今日(3/10)の日付は表示されない
        $response->assertDontSee($baseDate->format('Y/m/d'));
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_daily_attendance_page_next_button_shows_next_day_records()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 3, 10, 9, 0, 0));

        // 1. 管理者ユーザーにログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);
        $this->actingAs($admin);

        // 一般ユーザーを1人作成
        $user = User::factory()->create([
            'name' => '翌日ユーザー',
            'role' => 0,
        ]);

        // 「今日」となる基準日
        $baseDate = Carbon::today();
        $nextDate = $baseDate->copy()->addDay();

        // 翌日（3/11）の勤怠
        $nextAttendance = Attendance::create([
            'user_id'    => $user->id,
            'date'       => $nextDate,
            'work_start' => $nextDate->copy()->setTime(9, 0),
            'work_end'   => $nextDate->copy()->setTime(18, 0),
            'comment'    => null,
            'status'     => 3,
        ]);

        // 2. 勤怠一覧画面を開く
        // 3. 「翌日」ボタンを押す
        $response = $this->get(route('admin.dashboard', [
            'year' => $baseDate->year,
            'month' => $baseDate->month,
            'day' => $baseDate->day,
            'move' => 'next',
        ]));

        $response->assertOk();

        // 翌日の日付の勤怠情報が表示される
        $response->assertSee($nextDate->format('Y/m/d'));
        $response->assertSee($nextAttendance->work_start->format('H:i'));
        $response->assertSee($nextAttendance->work_end->format('H:i'));

        // 今日(3/10)の日付は表示されない
        $response->assertDontSee($baseDate->format('Y/m/d'));
    }
}
