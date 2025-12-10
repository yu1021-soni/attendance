<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserApplicationController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminApprovalController;

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

    Route::get('/', [UserAttendanceController::class, 'create'])->name('attendance.create'); //ok

    Route::post('/attendance/checkin', [UserAttendanceController::class, 'checkIn'])->name('work.start'); //ok

    Route::post('/attendance/checkout', [UserAttendanceController::class, 'checkOut'])->name('work.end'); //ok

    Route::post('/attendance/break/start', [UserAttendanceController::class, 'breakIn'])->name('break.start'); //ok

    Route::post('/attendance/break/end',   [UserAttendanceController::class, 'breakOut'])->name('break.end'); //ok

    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index'); //ok

    //Route::post('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index'); //使ってない？？

    Route::get('/correction-request', [UserApplicationController::class, 'create'])->name('correction.create'); //ok

    // attendance がない日の新規修正申請画面
    Route::get('/correction-request/new', [UserApplicationController::class, 'createNew'])
        ->name('correction.createNew'); //ok

    // 新規修正申請の保存
    Route::post('/correction-request/new', [UserApplicationController::class, 'newStore'])
        ->name('correction.newStore'); //ok

    Route::get('/correction-request/{id}', [UserApplicationController::class, 'store'])->name('correction.store'); //ok

    Route::post('/wait-approval', [UserApplicationController::class, 'show'])->name('wait.approval'); //ok
});

Route::get('/admin/login',  [AdminLoginController::class, 'showLoginForm'])->name('admin.login'); //ok
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login'); //ok

Route::middleware(['auth', 'admin'])
    ->group(function () {

        Route::post('/admin/logout', [AdminLoginController::class, 'logout'])
        ->name('admin.logout'); //ok

        Route::get('/admin/dashboard', [AdminAttendanceController::class, 'index'])
            ->name('admin.dashboard'); //ok

        Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.show'); //ok

        // ★ 追加：勤怠記録がない日の 修正申請（新規）フォーム表示
        Route::get('/admin/correction-request/new', [AdminAttendanceController::class, 'createNew'])
        ->name('admin.createNew');

        // ★ 追加：勤怠記録がない日の 修正申請（新規）登録
        Route::post('/admin/correction-request/new', [AdminAttendanceController::class, 'newStore'])
        ->name('admin.newStore');

        Route::post('/admin/attendance/{id}/correction', [AdminAttendanceController::class, 'updateCorrection'])->name('admin.correction'); //ok

        Route::get('/admin/staff', [AdminStaffController::class, 'index'])->name('staff.index'); //ok

        Route::get('/admin/staff/{id}/attendance', [AdminStaffController::class, 'show'])->name('staff.show'); //ok

        Route::get('/admin/attendance/{id}/export-csv', [AdminStaffController::class, 'exportCsv'])
        ->name('attendances.export'); //ok

        Route::get('/admin/correction-request', [AdminApprovalController::class, 'index'])->name('approval.index'); //ok

        Route::get('/admin/correction-request/{id}', [AdminApprovalController::class, 'show'])->name('approval.show'); //ok

        Route::post('/admin/correction-request/{id}/approve', [AdminApprovalController::class, 'approve'])->name('admin.approval'); //ok
    });