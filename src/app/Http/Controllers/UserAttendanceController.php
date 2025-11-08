<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;

class UserAttendanceController extends Controller
{
    public function create(Request $request)
    {
        $userId = $request->user()->id;
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

    public function checkIn(Request $request)
    {
        $userId = $request->user()->id;
        $todayDate = Carbon::today()->toDateString();

        // 出勤が無ければ作成
        Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $todayDate],
            ['work_start' => now(), 'work_time_total' => 0,
            'status' => Attendance::STATUS_ON, // 出勤中
            ]
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

        $attendance->status = Attendance::STATUS_BREAK; // 休憩中
        $attendance->save();

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

        $attendance->status = Attendance::STATUS_ON; // 出勤中に戻る
        $attendance->save();


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

        return redirect()->route('attendance.index');
    }
}
