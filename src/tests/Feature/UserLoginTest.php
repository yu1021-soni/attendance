<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class UserLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_login_email_required()
    {

        //1. ユーザを登録する
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // ログインページを開く
        $response = $this->from('/login')

            //2. メールアドレス以外のユーザ情報を入力する
            //3. ログインの処理を行う
            ->post('/login', [
                'email' => '',
                'password' => 'password123',
            ]);

        // /loginにリダイレクト
        $response->assertRedirect('/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors('email');
    }

    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_login_password_required() {

        //1. ユーザを登録する
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // ログインページを開く
        $response = $this->from('/login')

            //2. パスワード以外のユーザ情報を入力する
            //3. ログインの処理を行う
            ->post('/login',[
            'email' => 'test@example.com',
            'password' => '',
            ]);

        // /loginにリダイレクト
        $response->assertRedirect('/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors('password');
    }

    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_login_input_information_error()
    {
        //1. ユーザを登録する
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // ログインページを開く
        $response = $this->from('/login')

            //2. 誤ったメールアドレスの情報を入力する
            //3. ログインの処理を行う
            ->post('/login', [
                'email' => 'aaa@aaa.com',
                'password' => 'password123',
            ]);

        // /loginにリダイレクト
        $response->assertRedirect('/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors(['email']);
    }
}
