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
        // 年月日を取得
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $day   = (int) $request->query('day', now()->day);

        // 日付作成
        $date = Carbon::create($year, $month, $day);

        // 前日・翌日の移動
        $move = $request->query('move');
        if ($move === 'prev') {
            $date->subDay();
        } elseif ($move === 'next') {
            $date->addDay();
        }

        // 画面動かした日付で上書き
        $year  = $date->year;
        $month = $date->month;
        $day   = $date->day;

        // 勤怠データ取得
        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('date', $date->toDateString())
            ->get();

        return view('admin_attendance_list', compact('year', 'month', 'day', 'attendances'));
    }

    public function show($id) {
        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請があるかどうか
        $correction = Correction::where('attendance_id', $attendance->id)
            //条件
            ->where('user_id', $attendance->user_id)
            // 最新1件だけ取る
            ->latest()->first();


        $status = $correction?->status;

        return view('admin_attendance_show', [
            'attendance' => $attendance,
            'status' => $status,
            'correction' => $correction,
        ]);
    }

    public function updateCorrection(CorrectionRequest $request,$id) {

        // 勤怠を取得
        $attendance = Attendance::with('rests')->findOrFail($id);

        // バリデーション
        $validated = $request->validate([
            'work_start' => ['nullable', 'date_format:H:i'],
            'work_end'   => ['nullable', 'date_format:H:i'],
            'comment'    => ['nullable', 'string', 'max:255'],
        ]);

        // Laravel に最初から用意されている標準機能（デフォルト）。
        // フォーム入力が「空じゃないか」をチェックするための関数。
        if ($request->filled('work_start')) {
            $attendance->work_start = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['work_start']);
        }

        if ($request->filled('work_end')) {
            $attendance->work_end = Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $validated['work_end']);
        }

        if ($request->filled('comment')) {
            $attendance->comment = $validated['comment'];
        }

        $attendance->save();

        return redirect()
            ->route('admin.show', $attendance->id)
            ->with('success', '修正を反映しました');
    }
}
