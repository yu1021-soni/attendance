<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\CorrectionRequest;

class UserApplicationController extends Controller
{
    ///correction-request/10 -> $id=10
    public function store($id) {

        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        return view('user_attendance_show', [
            'attendance' => $attendance,
        ]);
    }

    public function show(CorrectionRequest $request) {

    }
}
