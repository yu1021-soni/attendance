<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class CheckOutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 退勤ボタンが正しく機能する
    public function test_work_end_button_updates_attendance_record() {

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

        // 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        // 2. 画面に「退勤」ボタンが表示されていることを確認する
        $response->assertSee('退勤');

        // 3. 退勤の処理を行う
        $response = $this->post(route('work.end'));
        $response->assertStatus(302);

        // リダイレクト先の画面を開く
        $pageResponse = $this->get(route('attendance.create'));
        $pageResponse->assertOk();

        // 画面上に「退勤」ボタンが表示され、処理後に画面上に表示されるステータスが「退勤済」になる
        $pageResponse->assertSee('退勤済');
    }

    // 退勤時刻が勤怠一覧画面で確認できる
    public function test_work_end_time_is_visible_on_index() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 1));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 1. ステータスが勤務外のユーザーにログインする
        $this->actingAs($user);

        //勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertStatus(200);

        // 2. 出勤と退勤の処理を行う

        // 出勤の処理を行う
        $postResponse = $this->post(route('work.start'));
        $postResponse->assertStatus(302);

        // 退勤時間
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 1, 11));

        // 退勤の処理を行う
        $postResponse = $this->post(route('work.end'));
        $postResponse->assertStatus(302);

        // 3. 勤怠一覧画面から退勤の日付を確認する
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('01/01');

        // 勤怠一覧画面に退勤時刻が正確に記録されている
        $response->assertSee('01:11');
    }
}
