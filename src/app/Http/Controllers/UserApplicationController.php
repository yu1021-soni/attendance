<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Correction;
use App\Http\Requests\CorrectionRequest;

class UserApplicationController extends Controller
{
    ///correction-request/10 -> $id=10
    public function store($id) {

        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請があるかどうか
        $correction = Correction::where('attendance_id', $attendance->id)
            //条件
            ->where('user_id', auth()->id())
            // 最新1件だけ取る
            ->latest()->first();


        $status = $correction?->status;

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status' => $status,
        ]);
    }

    public function show(CorrectionRequest $request) {

    }
}
