<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // 管理者なら /admin/dashboard へリダイレクト
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        // 一般ユーザーは Fortify の home 設定先へ (例: /attendance)
        return redirect()->intended(config('fortify.home'));
    }
}
