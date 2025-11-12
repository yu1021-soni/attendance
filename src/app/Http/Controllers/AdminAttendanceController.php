<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('user')->orderBy('date', 'desc')->paginate(20);
        return view('admin_attendance_list', compact('attendances')); // ← admin_attendance_list.blade.php
    }
}
