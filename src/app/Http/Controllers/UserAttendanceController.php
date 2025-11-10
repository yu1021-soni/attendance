<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class UserAttendanceController extends Controller
{
    public function create(Request $request) {
        $userId = $request->user()->id;

        //現在時刻
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        // DB保存のstatusを優先
        $status = $attendance->status ?? Attendance::STATUS_OFF;

        return view('user_attendance', [
            'now'        => now(),
            'status'     => $status,
            'attendance' => $attendance,
        ]);
    }

    public function checkIn(Request $request) {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        // 出勤が無ければ作成
        Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $todayDate],
            ['work_start' => now(), 'work_time_total' => 0,
            'status' => Attendance::STATUS_ON, // 出勤中
            ]
        );

        return redirect()->route('attendance.create');
    }

    public function breakIn(Request $request) {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        // 出勤中、退勤してない、休憩中でない → 休憩開始
        if (
            $attendance &&
            $attendance->work_start &&
            !$attendance->work_end &&
            !$attendance->rests()->whereNull('rest_end')->exists()
        ) {
            Rest::create([
                'attendance_id'   => $attendance->id,
                'rest_start'      => now(),
                'rest_time_total' => 0,
            ]);
        }

        $attendance->status = Attendance::STATUS_BREAK; // 休憩中
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function breakOut(Request $request) {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        if ($attendance) {
            // 未終了の休憩を取得
            $rest = $attendance->rests()
                ->whereNull('rest_end')
                ->first();

            if ($rest) {
                $rest->rest_end = now();
                $rest->rest_time_total = $rest->rest_start->diffInMinutes($rest->rest_end);
                $rest->save();
            }
        }

        $attendance->status = Attendance::STATUS_ON; // 出勤中に戻る
        $attendance->save();


        return redirect()->route('attendance.create');
    }


    public function checkOut(Request $request) {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        if (!$attendance || $attendance->work_end) {
            return redirect()->route('attendance.create');
        }

        // 未終了の休憩を締める
        $rest = $attendance->rests()->whereNull('rest_end')->first();
        if ($rest) {
            $rest->rest_end = now();
            $rest->rest_time_total = $rest->rest_start->diffInMinutes($rest->rest_end);
            $rest->save();
        }

        // 退勤
        $attendance->work_end = now();
        $attendance->work_time_total = $attendance->calcWorkMinutes(); // 休憩差し引き確定
        $attendance->status = Attendance::STATUS_DONE; // 退勤済み

        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function index(Request $request) {

        $userId = $request->user()->id;

        // いま表示する年月（最初は今月）
        $year  = (int) $request->session()->get('ym_year', now()->year);
        $month = (int) $request->session()->get('ym_month', now()->month);
        $cursor = \Carbon\Carbon::create($year, $month, 1);

        // 前月/翌月ボタン対応（?move=prev / next）
        if ($request->query('move') === 'prev') $cursor = $cursor->subMonthNoOverflow();
        if ($request->query('move') === 'next') $cursor = $cursor->addMonthNoOverflow();

        // 決まった年月をセッションに保存＆変数更新
        $year  = $cursor->year;
        $month = $cursor->month;
        $request->session()->put('ym_year', $year);
        $request->session()->put('ym_month', $month);

        $start = $cursor->copy()->startOfMonth();
        $end   = $cursor->copy()->endOfMonth();

        // その月の自分の勤怠を取得（休憩も一緒に）
        $attendances = Attendance::with('rests')
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date', 'asc')
            ->get();

        // 日付 → 勤怠 の対応表（探しやすくするだけ）
        $byDate = [];
        foreach ($attendances as $attendanceOfDay) {

            // date が Carbon なら toDateString()、文字列ならそのまま
            $key = $attendanceOfDay->date instanceof \Carbon\Carbon
                ? $attendanceOfDay->date->toDateString()
                : (string) $attendanceOfDay->date;

            $byDate[$key] = $attendanceOfDay;
        }

        // 月の全日（yyyy-mm-dd の配列）…未打刻日は空欄出力に使う
        $days = [];
        for ($d = 1; $d <= $cursor->daysInMonth; $d++) {
            $days[] = $cursor->copy()->day($d)->toDateString();
        }

        // 既存の year / month をそのまま使いたいので渡す
        return view('user_attendance_list', compact('year', 'month', 'days', 'byDate'));
    }
}
