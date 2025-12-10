<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 自分が行った勤怠情報が全て表示されている
    public function test_attendance_index_displays_user_attendance_records() {

        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        // 1. 勤怠情報が登録されたユーザーにログインする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 12月1〜3日の勤怠を仮登録
        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::create(2025, 12, 1),
            'work_start' => Carbon::create(2025, 12, 1, 9),
            'status'     => 1,
        ]);
        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::create(2025, 12, 2),
            'work_start' => Carbon::create(2025, 12, 2, 9),
            'status'     => 1,
        ]);
        Attendance::create([
            'user_id'    => $user->id,
            'date'       => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'status'     => 1,
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.index'));
        $response->assertOk();

        // 3. 自分の勤怠情報がすべて表示されていることを確認する
        // 自分の勤怠情報が全て表示されている
        $response->assertSee('12/01');
        $response->assertSee('12/02');
        $response->assertSee('12/03');
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_attendance_index_displays_current_month() {

        // 1. ユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.index'));
        $response->assertOk();

        // 現在の月が表示されている
        $response->assertSee('2025/12');
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_button_displays_previous_month_records() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 前月の勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 11, 30),
            'work_start' => Carbon::create(2025, 11, 30, 9),
            'status' => 1,
        ]);

        // 2. 勤怠一覧ページを開く
        Carbon::setTestNow(Carbon::create(2025, 12, 10));
        $response = $this->get(route('attendance.index'));
        $response->assertOk();

        // 3. 「前月」ボタンを押す
        $response = $this->get(route('attendance.index', ['month' => 11]));
        $response->assertOk();

        // 前月の情報が表示されている
        $response->assertSee('11/30');
    }

    // 「翌月」を押下した時に表示月の翌月の情報が表示される
    public function test_next_month_button_displays_next_month_records() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 翌月の勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 1, 1),
            'work_start' => Carbon::create(2026, 1, 1, 9),
            'status' => 1,
        ]);

        // 2. 勤怠一覧ページを開く
        Carbon::setTestNow(Carbon::create(2025, 12, 10));
        $response = $this->get(route('attendance.index'));
        $response->assertOk();

        // 3. 「翌月」ボタンを押す
        $response = $this->get(route('attendance.index', ['month' => 1]));
        $response->assertOk();

        // 翌月の情報が表示されている
        $response->assertSee('01/01');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_attendance_index_detail_button_navigates_to_detail_page() {

        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 勤怠データを登録
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 1),
            'work_start' => Carbon::today()->setTime(9, 0),
            'work_end'   => Carbon::create(2025, 12, 1, 18),
            'status'     => Attendance::STATUS_DONE,
        ]);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('attendance.index', [
            'year'  => 2025,
            'month' => 12,
        ]));
        $response->assertOk();

        // 3. 「詳細」ボタンを押下する
        $response->assertSee('詳細');

        $expectedUrl = route('correction.store', ['id' => $attendance->id]);

        // URLが含まれていることを確認
        $response->assertSee($expectedUrl);

        // その日の勤怠詳細画面に遷移する
        $detailResponse = $this->get($expectedUrl);
        $detailResponse->assertOk();
    }
}
