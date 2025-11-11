<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserApplicationController extends Controller
{
    public function store(Request $request) {
        return view ('user_attendance_show');
    }
}
