<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminGetUserInfoTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_view_all_general_users_with_name_and_email() {

        $user1 = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $user2 = User::factory()->create([
            'name' => 'テスト次郎',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 1. 管理者でログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. スタッフ一覧ページを開く
        $response = $this->get(route('staff.index'));
        $response->assertOk();

        // 全ての一般ユーザーの氏名とメールアドレスが正しく表示されている
        $response->assertSeeText($user1->name);
        $response->assertSeeText($user1->email);
        $response->assertSeeText($user2->name);
        $response->assertSeeText($user2->email);
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_attendance_records_are_displayed_correctly() {

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

        // 1. 管理者でログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 選択したユーザーの勤怠一覧ページを開く
        $response = $this->get(route('staff.show', ['id' => $user->id]));
        $response->assertOk();

        // 勤怠情報が正確に表示される
        $response->assertSeeText($attendance->date->format('n/j'));
        $response->assertSeeText($attendance->work_start->format('H:i'));
        $response->assertSeeText($attendance->work_end->format('H:i'));
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_previous_month_button_displays_previous_month_records() {

        // 時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 前月の勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2025, 11, 30),
            'work_start' => Carbon::create(2025, 11, 30, 9),
            'work_end' => Carbon::create(2025, 11, 30, 18),
            'status' => 3,
        ]);

        // 1. 管理者でログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('staff.show', ['id' => $user->id]));
        $response->assertOk();

        // 3. 「前月」ボタンを押す
        $response = $this->get(route('staff.show', [
            'id'    => $user->id,
            'move'  => 'prev',
        ]));
        $response->assertOk();

        // 前月の情報が表示されている
        $response->assertSee('11/30');
    }

    // 「翌月」を押下した時に表示月の翌月の情報が表示される
    public function test_next_month_button_displays_next_month_records() {

        // 時刻を固定
        Carbon::setTestNow(Carbon::create(2025, 12, 10));

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 翌月の勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 01, 30),
            'work_start' => Carbon::create(2026, 01, 30, 9),
            'work_end' => Carbon::create(2026, 01, 30, 18),
            'status' => 3,
        ]);

        // 1. 管理者でログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('staff.show', ['id' => $user->id]));
        $response->assertOk();

        // 3. 「翌月」ボタンを押す
        $response = $this->get(route('staff.show', [
            'id'    => $user->id,
            'move'  => 'next',
        ]));
        $response->assertOk();

        // 翌月の情報が表示されている
        $response->assertSee('1/30');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_detail_button_navigates_to_attendance_detail_page() {

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

        // 1. 管理者でログインする
        $admin = User::factory()->create([
            'role' => 1,
        ]);

        $this->actingAs($admin);

        // 2. 勤怠一覧ページを開く
        $response = $this->get(route('staff.show', ['id' => $user->id]));
        $response->assertOk();

        // 3. 「詳細」ボタンを押下する
        $response->assertSee('詳細');

        $response = $this->get(route('admin.show', ['id' => $attendance->id]));
        $response->assertOk();

        // その日の勤怠詳細画面に遷移する
        $response->assertSee($attendance->date->format('Y-m-d'));
        $response->assertSee($attendance->work_start->format('H:i'));
        $response->assertSee($attendance->work_end->format('H:i'));
    }
}
