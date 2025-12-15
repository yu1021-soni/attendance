<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;


class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_admin_login_email_required()
    {
        //1. 管理者ユーザを登録する
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        // ログインページを開く
        $response = $this->from('/admin/login')
            ->post('/login', [
                'email' => '',
                'password' => 'password123',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // 「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('email');
    }


    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_admin_login_password_required()
    {
        //1. 管理者ユーザを登録する
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1,
        ]);

        // ログインページを開く
        $response = $this->from('/admin/login')
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => '',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // 「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('password');
    }



    // 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_admin_login_input_information_error()
    {
        //1. 管理者ユーザを登録する
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 1, // 管理者
        ]);

        // ログインページを開く
        $response = $this->from('/admin/login')
            ->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'password123',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // 「ログイン情報が登録されていません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('email');
    }
}
