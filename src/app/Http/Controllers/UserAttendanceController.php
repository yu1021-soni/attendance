<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class UserAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        // 今日の勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        // 休憩中かどうか
        $isRestingNow = false;
        if ($attendance) {
            $isRestingNow = $attendance->rests()
                ->whereNull('rest_end')
                ->exists();
        }

        // ステータス分類
        // 0=勤務外 / 1=出勤中 / 2=休憩中 / 3=退勤済
        $status = 0;

        if ($attendance?->work_start && !$attendance?->work_end && !$isRestingNow) {
            $status = 1; // 出勤中
        }
        if ($isRestingNow) {
            $status = 2; // 休憩中
        }
        if ($attendance?->work_end) {
            $status = 3; // 退勤済
        }

        return view('user_attendance', [
            'now'        => now(),
            'status'     => $status,
            'attendance' => $attendance,
        ]);
    }

    public function checkIn(Request $request)
    {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        // 出勤が無ければ作成
        Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $todayDate],
            ['work_start' => now(), 'work_time_total' => 0]
        );

        return redirect()->route('attendance.index');
    }

    public function breakIn(Request $request)
    {
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

        return redirect()->route('attendance.index');
    }

    public function breakOut(Request $request)
    {
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

        return redirect()->route('attendance.index');
    }


    public function checkOut(Request $request)
    {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->first();

        if (!$attendance || $attendance->work_end) {
            return redirect()->route('attendance.index');
        }

        // 未終了の休憩を締める
        $rest = $attendance->rests()
            ->whereNull('rest_end')
            ->first();

        if ($rest) {
            $rest->rest_end = now();
            $rest->rest_time_total = $rest->rest_start->diffInMinutes($rest->rest_end);
            $rest->save();
        }

        // 退勤時刻
        $attendance->work_end = now();
        $attendance->work_time_total =
            $attendance->work_start->diffInMinutes($attendance->work_end);

        $attendance->save();

        return redirect()->route('attendance.index');
    }
}
