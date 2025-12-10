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
            ['work_start' => now(),
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
        $attendance->status = Attendance::STATUS_DONE; // 退勤済み

        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // 表示する年月（初回は今月）
        // request->query('year')-> URL の ?year=○○ を読む
        //$request->query('year', 2025)-> yearが無かったら2025を使う
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // 前月・翌月の移動
        $move = $request->query('move');
        if ($move === 'prev') $month--; // -- = -1
        if ($move === 'next') $month++; // ++ = +1

        // 年の調整（12 → 次年 / 0 → 前年）
        if ($month === 0) {
            $month = 12;
            $year--;
        }
        if ($month === 13) {
            $month = 1;
            $year++;
        }

        // 月初・月末の日にちを取る
        $monthStart = Carbon::create($year, $month, 1);
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // 勤怠データ取得
        $attendanceList = Attendance::with('rests')
            ->where('user_id', $userId)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->where('status', Attendance::STATUS_DONE)
            ->get();

        // 日付 → 勤怠 の表
        $attendanceByDate = [];
        foreach ($attendanceList as $attendance) {
            $attendanceByDate[$attendance->date->toDateString()] = $attendance;
        }

        // 月の日付リスト
        $days = [];
        for ($day = 1; $day <= $monthStart->daysInMonth; $day++) {
            $days[] = Carbon::create($year, $month, $day)->toDateString();
        }

        return view('user_attendance_list', compact('year', 'month', 'days', 'attendanceByDate'));
    }
}
