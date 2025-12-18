<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Correction;

class UserDetailCorrectionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_work_start_is_after_work_end()
    {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

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

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 3. 出勤時間を退勤時間より後に設定する
        // 4. 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), [
                'attendance_id' => $attendance->id,
                'work_start' => '19:00',
                'work_end' => '18:00',
                'comment' => 'テスト',
            ]);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 「出勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('work_start');
    }



    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_rest_start_is_after_work_end()
    {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

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

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
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
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクトされること
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 「休憩時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('rests.0.rest_start');
    }



    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_validation_fails_when_rest_end_is_after_work_end()
    {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

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

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
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
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 「休憩時間もしくは退勤時間が不適切な値です」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('rests.0.rest_end');
    }



    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_validation_fails_when_comment_is_empty()
    {

        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

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
            'rest_end' => Carbon::create(2025, 12, 3, 13, 0), // 13:00
        ]);

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
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

        // 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 「備考を記入してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('comment');
    }

    // 修正申請処理が実行される
    public function test_correction_request_is_successfully_processed() {

        // 現在時刻を固定
        $attendanceDate = Carbon::create(2025, 12, 10);

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
            'rest_start' => Carbon::create(2025, 12, 3, 12, 0), // 12:00
            'rest_end'   => Carbon::create(2025, 12, 3, 13, 0), // 13:00
        ]);

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細を修正し保存処理をする

        // 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 勤怠修正
        $postData = [
            'attendance_id' => $attendance->id,

            'work_start' => '08:00',
            'work_end' => '17:00',

            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],

            'comment' => 'テスト',
        ];

        // 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 3. 管理者ユーザーで承認画面と申請一覧画面を確認する

        $admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role'     => 1,
        ]);

        $this->actingAs($admin);

        // 修正申請が実行され、管理者の承認画面と申請一覧画面に表示される

        $adminResponse = $this->get(route('approval.index', ['tab' => 'pending']));
        $adminResponse->assertOk();

        $adminResponse->assertSee('テスト');
        $adminResponse->assertSee($user->name);
        $adminResponse->assertSee($attendanceDate->format('Y/m/d'));
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_pending_tab_displays_only_users_correction_requests() {

        // 現在時刻を固定
        $attendanceDate = Carbon::create(2025, 12, 10);

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
            'rest_start' => Carbon::create(2025, 12, 10, 12, 0), // 12:00
            'rest_end'   => Carbon::create(2025, 12, 10, 13, 0), // 13:00
        ]);

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細を修正し保存処理をする

        // 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 勤怠修正
        $postData = [
            'attendance_id' => $attendance->id,

            'work_start' => '08:00',
            'work_end' => '17:00',

            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],

            'comment' => 'テスト',
        ];

        // 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));


        //3. 申請一覧画面を確認する
        $listResponse = $this->get(
            route('correction.create', ['tab' => 'pending'])
        );
        $listResponse->assertOk();

        // 申請一覧に自分の申請が全て表示されている
        $listResponse->assertSee('テスト');
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_approved_tab_displays_only_admin_approved_requests() {

        // 現在時刻を固定
        $attendanceDate = Carbon::create(2025, 12, 10);

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
            'rest_start' => Carbon::create(2025, 12, 3, 12, 0), // 12:00
            'rest_end'   => Carbon::create(2025, 12, 3, 13, 0), // 13:00
        ]);

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細を修正し保存処理をする

        // 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 勤怠修正
        $postData = [
            'attendance_id' => $attendance->id,

            'work_start' => '08:00',
            'work_end' => '17:00',

            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],

            'comment' => 'テスト',
        ];

        // 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 管理者承認済みに更新
        $correction = Correction::first();
        $correction->update([
            'status' => Correction::STATUS_APPROVED,
        ]);

        // 3. 申請一覧画面を開く
        $listResponse = $this->get(
            route('correction.create', ['tab' => 'approved'])
        );
        $listResponse->assertOk();

        // 4. 管理者が承認した修正申請が全て表示されていることを確認
        // 承認済みに管理者が承認した申請が全て表示されている
        $listResponse->assertSee('テスト');
    }

    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_show_button_navigates_to_correction_detail_page() {

        // 現在時刻を固定
        $attendanceDate = Carbon::create(2025, 12, 10);

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
            'rest_start' => Carbon::create(2025, 12, 3, 12, 0), // 12:00
            'rest_end'   => Carbon::create(2025, 12, 3, 13, 0), // 13:00
        ]);

        // 1. 勤怠情報が登録されたユーザーにログインをする
        $this->actingAs($user);

        // 2. 勤怠詳細を修正し保存処理をする

        // 勤怠詳細ページを開く
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));
        $response->assertOk();

        // 勤怠修正
        $postData = [
            'attendance_id' => $attendance->id,

            'work_start' => '08:00',
            'work_end' => '17:00',

            'rests' => [
                [
                    'rest_start' => '12:00',
                    'rest_end' => '13:00',
                ],
            ],

            'comment' => 'テスト',
        ];

        // 保存処理をする
        $response = $this
            ->from(route('correction.show', ['id' => $attendance->id]))
            ->post(route('wait.approval'), $postData);

        // 元の画面にリダイレクト
        $response->assertRedirect(route('correction.show', ['id' => $attendance->id]));

        // 3. 申請一覧画面を開く
        $listResponse = $this->get(route('correction.create', ['tab' => 'pending']));
        $listResponse->assertOk();

        // 4. 「詳細」ボタンを押す
        $response = $this->get(route('correction.show', ['id' => $attendance->id]));

        // 勤怠詳細画面に遷移する
        $response->assertOk();
    }
}
