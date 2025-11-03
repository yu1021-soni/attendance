<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // アクションをFortifyに紐付け
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // 認証用ビュー（必要に応じて）
        Fortify::loginView(fn() => view('auth.login'));
        Fortify::registerView(fn() => view('auth.register'));
    }
}
