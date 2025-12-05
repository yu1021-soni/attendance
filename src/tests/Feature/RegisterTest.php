<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    //array 配列を受け取る
    //$override = []  何も渡されなかったら空の配列
    public function validPayload(array $override = []): array
    {
        return array_merge([
            'name' => '山田 太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $override);
    }

    //名前未入力の場合、バリデーションメッセージが表示される
    public function test_name_is_required()
    {

        // 会員登録ページを開く
        $response = $this->from(route('register'))

            //１. 名前以外のユーザ情報を入力する
            ->post(route('register'), $this->validPayload(['name' => '']));

        //２. 会員登録の処理を行う
        $response->assertRedirect(route('register')); //エラー時に戻るページ

        //「お名前を入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('name');
    }

    //メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required()
    {

        // 会員登録ページを開く
        $response = $this->from(route('register'))

            //1. メールアドレス以外のユーザ情報を入力する
            ->post(route('register'), $this->validPayload(['email' => '']));

        //２. 会員登録の処理を行う
        $response->assertRedirect(route('register'));

        //「メールアドレスを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('email');
    }

    //パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required()
    {

        // 会員登録ページを開く
        $response = $this->from(route('register'))

            //１. パスワード以外のユーザ情報を入力する
            ->post(route('register'), $this->validPayload([
                'password' => '',
                'password_confirmation' => '',
            ]));

        //２. 会員登録の処理を行う
        $response->assertRedirect(route('register'));

        //「パスワードを入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('password');
    }

    //パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_password_must_be_at_least_8_chars()
    {

        // 会員登録ページを開く
        $response = $this->from(route('register'))

            //１. パスワードを8文字未満にし、ユーザ情報を入力する
            ->post(route('register'), $this->validPayload([
                'password' => '1111',
                'password_confirmation' => '1111',
            ]));

        //２. 会員登録の処理を行う
        $response->assertRedirect(route('register'));

        //「パスワードは8文字以上で入力してください」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('password');
    }

    //パスワードが一致しない場合、バリデーションメッセージが表示される
    public function test_password_confirmation_must_match()
    {

        // 会員登録ページを開く
        $response = $this->from(route('register'))

            //1. 確認用のパスワードとパスワードを一致させず、ユーザ情報を入力する
            ->post(route('register'), $this->validPayload([
                'password_confirmation' => 'DIFFERENT',
            ]));

        //２. 会員登録の処理を行う
        $response->assertRedirect(route('register'));

        //「パスワードと一致しません」というバリデーションメッセージが表示される
        $response->assertSessionHasErrors('password');
    }

    //フォームに内容が入力されていた場合、データが正常に保存される
    public function test_register_success()
    {

        // 会員登録ページを開く
        $this->get(route('register'))->assertOk();

        //１. ユーザ情報を入力する
        $payload = $this->validPayload();

        //２. 会員登録の処理を行う
        $response = $this->from(route('register'))
            ->post(route('register'), $payload);

        // 入力が正しいのでバリデーションエラーがないことを確認
        $response->assertSessionHasNoErrors();

        // DBにユーザーが登録されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
        ]);

        // 勤怠登録画面に移動
        $response->assertRedirect(route('attendance.create'));
    }

    //クラスの中のメソッドから、自分のメソッドや機能を呼ぶときに $this->を使用
}
