<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class DateTimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // 現在の日時情報がUIと同じ形式で出力されている
    public function test_get_date()
    {
        // 現在時刻を固定
        Carbon::setTestNow(Carbon::create(2025,1,1,1,1,));

        // ユーザを登録する
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // ログインする
        $this->actingAs($user);

        //1. 勤怠打刻画面を開く
        $response = $this->get(route('attendance.create'));
        $response->assertOk();

        //2. 画面に表示されている日時情報を確認する
        $currentDate = Carbon::now()->format('Y年m月d日');
        $currentTime = Carbon::now()->format('H:i');

        //画面上に表示されている日時が現在の日時と一致する
        $response->assertSee($currentDate);
        $response->assertSee($currentTime);
    }
}
