<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    
    public function toResponse($request)
    {
        $user = $request->user();

        // 管理者ログイン画面から来た場合のみadmin側へ
        if (
            $user &&
            (int)$user->role === 1 &&
            session('admin_login') === true
        ) {
            return redirect()->route('admin.dashboard');
        }

        // 一般ユーザーは Fortify の home 設定先へ
        return redirect()->intended(config('fortify.home'));
    }
}
