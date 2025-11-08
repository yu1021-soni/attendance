<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAttendanceController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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

    Route::post('/work/start', [UserAttendanceController::class, 'checkIn'])->name('work.start');

    Route::post('/work/end', [UserAttendanceController::class, 'checkOut'])->name('work.end');

    Route::post('/break/start', [UserAttendanceController::class, 'breakIn'])->name('break.start');

    Route::post('/break/end',   [UserAttendanceController::class, 'breakOut'])->name('break.end');
});