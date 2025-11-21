<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffController extends Controller
{
    public function index() {

        // 管理者除外
        $users = User::where('role', '!=', 1)->get();

        return view('staff_list',['users' => $users]);
    }

    public function show($id) {

        $attendance = Attendance::findOrFail($id);
        $user = $attendance->user;

        return view('staff_detail', compact('attendance', 'user'));
    }
}
