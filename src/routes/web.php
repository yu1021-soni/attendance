<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\UserApplicationController;
use App\Http\Controllers\AdminLoginController;

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

    Route::post('attendance/break/end',   [UserAttendanceController::class, 'breakOut'])->name('break.end');

    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [UserAttendanceController::class, 'index'])->name('attendance.index');//記入まだ

    Route::get('/correction-request/{id}', [UserApplicationController::class, 'store'])->name('correction.store');
});

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm']);