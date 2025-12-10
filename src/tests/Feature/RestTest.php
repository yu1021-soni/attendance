<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class RestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 休憩ボタンが正しく機能する
    public function test_rest_start_button_creates_rest_record() {

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
            'status' => 1,
        ]);

        //1. ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        // 2. 画面に「休憩入」ボタンが表示されていることを確認する
        $response->assertSee('休憩入');

        // 3. 休憩の処理を行う
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        // リダイレクト先の画面を開く
        $pageResponse = $this->get(route('attendance.create'));
        $pageResponse->assertOk();

        // 画面上に表示されるステータスが「休憩中」になる
        $pageResponse->assertSee('休憩中');
    }

    // 休憩は一日に何回でもできる
    public function test_rest_can_be_started_multiple_times_in_one_day() {

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
            'status' => 1,
        ]);

        // 1. ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 2. 休憩入と休憩戻の処理を行う

        // 休憩入ボタン押下（休憩開始）
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        // 休憩戻ボタン押下
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 11));
        $response = $this->post(route('break.end'));
        $response->assertStatus(302);

        // 3. 「休憩入」ボタンが表示されることを確認する
        // 画面上に「休憩入」ボタンが表示される
        $response = $this->get(route('attendance.create'));
        $response->assertOk();
        $response->assertSee('休憩入');
    }

    // 休憩戻ボタンが正しく機能する
    public function test_rest_end_button_updates_rest_record() {

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
            'status' => 1,
        ]);

        // 1. ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 2. 休憩入の処理を行う
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        //3. 休憩戻の処理を行う
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 11));
        $response = $this->post(route('break.end'));
        $response->assertStatus(302);

        // 休憩戻ボタンが表示され、処理後にステータスが「出勤中」に変更される
        $response = $this->get(route('attendance.create'));
        $response->assertOk();
        $response->assertSee('出勤中');
    }

    // 休憩戻は一日に何回でもできる
    public function test_rest_can_be_ended_multiple_times_in_one_day() {

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
            'status' => 1,
        ]);

        // 1. ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);


        // 2. 休憩入と休憩戻の処理を行い、再度休憩入の処理を行う

        // 休憩入ボタン押下（休憩開始）
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        // 休憩戻ボタン押下
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 11));
        $response = $this->post(route('break.end'));
        $response->assertStatus(302);

        // 休憩入ボタン押下（休憩開始）
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        // 3. 「休憩戻」ボタンが表示されることを確認する
        // 画面上に「休憩戻」ボタンが表示される
        $response = $this->get(route('attendance.create'));
        $response->assertOk();
        $response->assertSee('休憩戻');
    }

    //休憩時刻が勤怠一覧画面で確認できる
    public function test_rest_times_are_visible_on_attendance_index() {

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
            'status' => 1,
        ]);

        // 1. ステータスが出勤中のユーザーにログインする
        $this->actingAs($user);

        // 2. 休憩入と休憩戻の処理を行う

        // 休憩入ボタン押下（休憩開始）
        $response = $this->post(route('break.start'));
        $response->assertStatus(302);

        // 休憩戻ボタン押下
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 11));
        $response = $this->post(route('break.end'));
        $response->assertStatus(302);

        // 退勤
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 2, 0));
        $response = $this->post(route('work.end'));
        $response->assertStatus(302);

        // 3. 勤怠一覧画面から休憩の日付を確認する
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('01/01');

        // 勤怠一覧画面に休憩時刻が正確に記録されている
        $response->assertSee('0:10');

    }
}
