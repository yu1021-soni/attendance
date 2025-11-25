<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Correction;
use App\Http\Requests\CorrectionRequest;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 年月日を取得（指定なければ今日）
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);
        $day   = (int) $request->query('day',   now()->day);

        // 日付作成
        $date = Carbon::create($year, $month, $day);

        // 前日・翌日の移動
        $move = $request->query('move');
        if ($move === 'prev') {
            $date->subDay();
        } elseif ($move === 'next') {
            $date->addDay();
        }

        // 画面用に年月日を上書き
        $year  = $date->year;
        $month = $date->month;
        $day   = $date->day;

        // 勤怠データ取得（ユーザー・休憩込み）
        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('date', $date->toDateString())
            ->get();

        return view('admin_attendance_list', compact('year', 'month', 'day', 'attendances'));
    }

    public function show($id)
    {
        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請があるかどうか
        $correction = Correction::where('attendance_id', $attendance->id)
            ->where('user_id', $attendance->user_id)
            ->latest()
            ->first();

        $status = $correction?->status;

        return view('admin_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction' => $correction,
        ]);
    }

    public function updateCorrection(CorrectionRequest $request, $id)
    {
        // 勤怠を取得
        $attendance = Attendance::with('rests')->findOrFail($id);

        // CorrectionRequest でバリデーション済み
        $validated = $request->validated();

        // 出勤時刻の更新
        if ($request->filled('work_start')) {
            $attendance->work_start = Carbon::parse(
                $attendance->date->format('Y-m-d') . ' ' . $validated['work_start']
            );
        }

        // 退勤時刻の更新
        if ($request->filled('work_end')) {
            $attendance->work_end = Carbon::parse(
                $attendance->date->format('Y-m-d') . ' ' . $validated['work_end']
            );
        }

        // 備考の更新
        if ($request->filled('comment')) {
            $attendance->comment = $validated['comment'];
        }

        // 勤怠保存
        $attendance->save();

        return redirect()
            ->route('admin.show', $attendance->id)
            ->with('success', '修正を反映しました');
    }
}
