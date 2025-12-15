<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as FortifyLoginResponse;
use App\Http\Responses\LoginResponse as CustomLoginResponse;


//提出前に消す
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            FortifyLoginResponse::class,
            CustomLoginResponse::class
        );

        $this->app->bind(
            \Laravel\Fortify\Http\Requests\RegisterRequest::class,
            \App\Http\Requests\RegisterRequest::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        //提出前に消す
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(1000)->by($request->email . $request->ip());
        });


        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    // ルート名で確実に
                    return redirect()->route('login');
                }
            };
        });

        // アクションをFortifyに紐付け
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::loginView(fn() => view('auth.user_login'));
        Fortify::registerView(fn() => view('auth.register'));

        Fortify::verifyEmailView(function () {
            return view('auth.mailhog');
        });

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return null; // 通常のログイン失敗
            }

            // 管理者ログイン画面かに一般ユーザーなら弾く
            if ($request->input('admin_login') === '1' && (int)$user->role !== 1) {
                $request->session()->forget('admin_login');

                throw ValidationException::withMessages([
                    'email' => '管理者ログイン画面からは一般ユーザーでログインできません',
                ]);
            }

            // 管理者ログイン
            if ($request->input('admin_login') === '1') {
                $request->session()->put('admin_login', true);
            } else {
                $request->session()->forget('admin_login');
            }

            return $user;
        });
    }
}
