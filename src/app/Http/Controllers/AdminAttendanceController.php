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
        // 対象の勤怠を取得（休憩も一緒に）
        $attendance = Attendance::with('rests')->findOrFail($id);

        // CorrectionRequest でバリデーション済みの値を取得
        $validated = $request->validated();

        // 日付を文字列で用意
        $dateStr = $attendance->date->format('Y-m-d');

        // 出勤・退勤を更新
        if (!empty($validated['work_start'])) {
            $attendance->work_start = Carbon::parse($dateStr . ' ' . $validated['work_start']);
        }

        if (!empty($validated['work_end'])) {
            $attendance->work_end = Carbon::parse($dateStr . ' ' . $validated['work_end']);
        }

        // 備考を更新
        if (array_key_exists('comment', $validated)) {
            $attendance->comment = $validated['comment'];
        }

        // 休憩の更新
        // フォームから送られてきた休憩配列
        $restsInput = $request->input('rests', []);

        foreach ($restsInput as $restData) {
            $restId    = $restData['id']         ?? null;
            $restStart = $restData['rest_start'] ?? null;
            $restEnd   = $restData['rest_end']   ?? null;

            // 既存のRestを探す
            $restModel = $restId
                ? $attendance->rests->firstWhere('id', $restId)
                : null;

            // 開始・終了どちらも空なら：
            // 既存行があれば削除してスキップ
            if (empty($restStart) && empty($restEnd)) {
                if ($restModel) {
                    $restModel->delete();
                }
                continue;
            }

            // 既存レコードがなければ新しく作成
            if (!$restModel) {
                $restModel = $attendance->rests()->make();
            }

            // rest_start / rest_end を日時で保存
            $restModel->rest_start = $restStart
                ? Carbon::parse($dateStr . ' ' . $restStart)
                : null;

            $restModel->rest_end = $restEnd
                ? Carbon::parse($dateStr . ' ' . $restEnd)
                : null;

            $restModel->save();
        }

        // 勤怠本体を保存
        $attendance->save();

        return redirect()
            ->route('admin.show', $attendance->id)
            ->with('success', '修正を反映しました');
    }
}