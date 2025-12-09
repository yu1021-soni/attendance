<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    //会員登録後、認証メールが送信される
    public function test_email_send()
    {

        //メール送信をフェイク
        Notification::fake();


        //1. 会員登録をする
        //会員登録のデータを用意
        $register = [
            'name'                  => '山田太郎',
            'email'                 => 'taro@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];
        //会員登録処理
        $response = $this->post(route('register'), $register);


        //2 認証メールを送信する
        //登録後リダイレクト
        $response->assertRedirect();
        //DBに登録されているか
        $user = User::where('email', 'taro@example.com')->first();
        $this->assertNotNull($user);

        //認証メールが送られたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
    public function test_email_mailhog_page()
    {

        // メール送信をフェイク
        Notification::fake();

        // まだメール認証していないユーザーを作成
        $user = User::factory()->unverified()->create();

        //1 メール認証導線画面を表示する
        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk();

        //2 「認証はこちらから」ボタンを押下する
        $response = $this->actingAs($user)
            ->post(route('verification.send'));

        //3 メール認証サイトを表示する
        $response->assertSessionHas('status', 'verification-link-sent');

        //認証メールが送られたか確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    //メール認証サイトのメール認証を完了すると、プロフィール設定画面に遷移する
    public function test_email_profile_page()
    {

        // まだメール認証していないユーザーを作成
        $user = User::factory()->unverified()->create();


        //1 メール認証を完了する
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->getKey(), 'hash' => sha1($user->email)]
        );

        // ユーザーが認証リンク
        $response = $this->actingAs($user)
            ->withSession(['url.intended' => route('attendance.create')])
            ->get($url);

        //2 勤怠登録画面を表示する
        $response->assertRedirect(route('attendance.create'));
    }
}
