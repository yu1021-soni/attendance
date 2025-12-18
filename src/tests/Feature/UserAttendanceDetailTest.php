<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class UserAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_attendance_detail_displays_logged_in_users_name() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show',['id' => $attendance->id]));
        $response->assertOk();

        // 3. 名前欄を確認する
        // 名前がログインユーザーの名前になっている
        $response->assertSee('山田太郎');

    }

    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_attendance_detail_displays_selected_date_correctly() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 日付欄を確認する
        // 日付が選択した日付になっている
        $response->assertSee('12月3日');
    }

    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_displays_correct_work_start_and_end_times() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 出勤・退勤欄を確認する
        // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_displays_correct_rest_times() {

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($user);

        // 勤怠データ作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        $attendance->rests()->create([
            'rest_start' => Carbon::create(2025, 12, 3, 12, 0), // 12:00
            'rest_end'   => Carbon::create(2025, 12, 3, 13, 0), // 13:00
        ]);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 休憩欄を確認する
        // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
