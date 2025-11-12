<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * 管理者かどうかをチェックする
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 未ログイン または 管理者ではない場合はログインページへ
        if (!$user || !method_exists($user, 'isAdmin') || !$user->isAdmin()) {
            return redirect()->route('admin.login');
        }

        // OKなら次へ進む
        return $next($request);
    }
}
