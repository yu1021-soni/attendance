<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserApplicationController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [UserAttendanceController::class, 'create'])->name('attendance.create');

    Route::post('/attendance/checkin', [UserAttendanceController::class, 'checkIn'])->name('work.start');

    Route::post('/attendance/checkout', [UserAttendanceController::class, 'checkOut'])->name('work.end');

    Route::post('/attendance/break/start', [UserAttendanceController::class, 'breakIn'])->name('break.start');

    Route::post('/attendance/break/end',   [UserAttendanceController::class, 'breakOut'])->name('break.end');

    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');//記入まだ

    // ★attendance がない日の新規修正申請画面（ここを追加）
    Route::get('/correction-request/new', [UserApplicationController::class, 'createNew'])
        ->name('correction.createNew'); // 記入まだ

    Route::get('/correction-request/{id}', [UserApplicationController::class, 'store'])->name('correction.store');

    Route::post('/wait-approval', [UserApplicationController::class, 'show'])->name('wait.approval');

    // 新規修正申請の保存 POST
    Route::post('/correction-request/new', [UserApplicationController::class, 'newStore'])
        ->name('correction.newStore'); // 記入まだ

    Route::get('/correction-request', [UserApplicationController::class, 'create'])->name('correction.create');
});

Route::get('/admin/login',  [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login']);

Route::middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/admin/dashboard', [AdminAttendanceController::class, 'index'])
            ->name('admin.dashboard');

        Route::post('/admin/logout', [AdminLoginController::class, 'logout'])
            ->name('admin.logout'); // 記入まだ
    });