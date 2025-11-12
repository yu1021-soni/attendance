<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AdminLoginController extends Controller
{
    public function showLoginForm() {
        return view('auth.admin_login');
    }

    public function login(AdminLoginRequest $request)
    {

        // !は否定
        // Auth::attempt() フォームで入力された情報（メール・パスワード）が正しいか確認する
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()
                ->withErrors(['email' => 'ログイン情報が登録されていません'])
                ->withInput();
        }

        return redirect()->route('admin.dashboard');
    }
}
