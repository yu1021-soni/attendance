<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserAttendanceController extends Controller
{
    public function index()
    {
        // 打刻画面（仮のビュー名）
        return view('user_attendance');
    }
}
