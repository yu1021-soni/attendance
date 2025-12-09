<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminDetailCorrectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_attendance_detail_page_displays_selected_record() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 詳細画面の内容が選択した情報と一致する
        $response->assertSee($attendance->date->format('Y-m-d'));
        $response->assertSee($attendance->work_start->format('H:i'));
        $response->assertSee($attendance->work_end->format('H:i'));
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_error_when_work_start_is_after_work_end() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 12, 3),
            'work_start' => Carbon::create(2025, 12, 3, 9),
            'work_end' => Carbon::create(2025, 12, 3, 18),
            'status' => 3,
        ]);

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 出勤時間を退勤時間より後に設定する
        $postData = [
            'attendance_id' => $attendance->id,
            'work_start' => '19:00',
            'work_end' => '18:00',
            'comment' => 'テスト',
        ];

        // 4. 保存処理をする
        $response = $this->post(route('admin.correction', ['id' => $attendance->id]), $postData);

        // 「出勤時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('work_start');
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_error_when_rest_start_is_after_work_end() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

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

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 休憩開始時間を退勤時間より後に設定する
        $postData = [
            'attendance_id' => $attendance->id,
            'work_start' => '09:00',
            'work_end' => '18:00',
            'rests' => [
                [
                    'rest_start' => '19:00',
                    'rest_end' => '20:00',
                ],
            ],
            'comment' => 'テスト',
        ];

        // 4. 保存処理をする
        $response = $this->post(route('admin.correction', ['id' => $attendance->id]), $postData);

        // 「休憩時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('rests.0.rest_start');
    }

    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_error_when_rest_end_is_after_work_end() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

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

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 休憩終了時間を退勤時間より後に設定する
        $postData = [
            'attendance_id' => $attendance->id,
            'work_start' => '09:00',
            'work_end' => '18:00',
            'rests' => [
                [
                    'rest_start' => '17:00',
                    'rest_end' => '20:00',
                ],
            ],
            'comment' => 'テスト',
        ];

        // 4. 保存処理をする
        $response = $this->post(route('admin.correction', ['id' => $attendance->id]), $postData);

        // 「休憩時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('rests.0.rest_end');
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_validation_error_when_comment_is_empty() {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 9, 0, 0));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

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

        // 1. 管理者ユーザーにログインをする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 備考欄を未入力のまま保存処理をする
        $postData = [
            'attendance_id' => $attendance->id,
            'work_start' => '09:00',
            'work_end' => '18:00',
            'rests' => [
                [
                    'rest_start' => '13:00',
                    'rest_end' => '14:00',
                ],
            ],
            'comment' => '',
        ];

        $response = $this->post(route('admin.correction', ['id' => $attendance->id]), $postData);

        // 「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('comment');
    }
}
