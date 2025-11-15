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
    

    public function show(Request $request)
    {
        // 1. 元の勤怠データを取得（hidden で渡した id を使う想定）
        $attendance = Attendance::findOrFail($request->attendance_id);

        // 2. 修正申請を作成
        $correction = new Correction;
        $correction->user_id       = auth()->id();
        $correction->attendance_id = $attendance->id;

        // 古い値（old_*）
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;

        // 新しい値（new_*）
        $correction->new_work_start = $request->work_start;
        $correction->new_work_end   = $request->work_end;

        // 備考
        $correction->comment = $request->textarea;

        // ステータスは必ず pending
        $correction->status = Correction::STATUS_PENDING;

        $correction->save();

        // 3. 承認待ち画面へ
        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $correction->status,
        ]);
    }
}
