<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 出勤ボタンが正しく機能する
    public function test_work_start_button_creates_attendance() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025,1,1,1,1));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);
        $response->assertSee('勤務外');

        //2. 画面に「出勤」ボタンが表示されていることを確認する
        $response->assertSee('出勤');

        //3. 出勤の処理を行う
        $postResponse = $this->post(route('work.start'));

        $postResponse->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => '2025-01-01 00:00:00',
            'status'  => 1,
        ]);

        $screen = $this->get(route('attendance.create'));
        $screen->assertStatus(200);

        //画面上に「出勤」ボタンが表示され、処理後に画面上に表示されるステータスが「勤務中」になる
        $screen->assertSee('出勤中');
    }

    // 出勤は一日一回のみできる
    public function test_work_start_only_once_per_day() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'work_start' => Carbon::today()->setTime(1, 0),
            'work_end' => null,
            'comment' => null,
            'status' => 3,
        ]);

        //1. ステータスが退勤済のユーザーにログインする
        $this->actingAs($user);

        // 2. 勤務ボタンが表示されないことを確認する
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);
        $response->assertSee('退勤済');

        // 画面上に「出勤」ボタンが表示されない
        $response->assertDontSee('出勤');
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function test_work_start_time_is_visible_on_index() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        //2. 出勤の処理を行う
        $postResponse = $this->post(route('work.start'));

        $postResponse->assertStatus(302);

        // 3. 勤怠一覧画面から出勤の日付を確認する
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('01/01');

        // 勤怠一覧画面に出勤時刻が正確に記録されている
        $response->assertSee('01:01');
    }
}
