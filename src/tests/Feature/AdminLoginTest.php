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
            ->post('/admin/login', [
                'email' => '',
                'password' => 'password123',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors('email');
    }



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
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => '',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors('password');
    }




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
            ->post('/admin/login', [
                'email' => 'wrong@example.com',
                'password' => 'password123',
            ]);

        // /admin/loginにリダイレクト
        $response->assertRedirect('/admin/login');

        // バリデーションメッセージ表示
        $response->assertSessionHasErrors('email');
    }
}
