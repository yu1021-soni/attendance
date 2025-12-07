<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class StatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function test_off_duty_status() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1,));

        // ユーザを登録する
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
            'status' => 0,
        ]);

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        //3. 画面に表示されているステータスを確認する
        // 画面上に表示されているステータスが「勤務外」となる
        $response->assertSee('勤務外');
    }

    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1,));

        // ユーザを登録する
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
            'status' => 1,
        ]);

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        //3. 画面に表示されているステータスを確認する
        // 画面上に表示されているステータスが「出勤中」となる
        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function test_break_status() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1,));

        // ユーザを登録する
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
            'status' => 2,
        ]);

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        //3. 画面に表示されているステータスを確認する
        // 画面上に表示されているステータスが「休憩中」となる
        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function test_leaving_work_status() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1,));

        // ユーザを登録する
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

        //1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //2. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        //3. 画面に表示されているステータスを確認する
        // 画面上に表示されているステータスが「退勤済」となる
        $response->assertSee('退勤済');
    }
}
