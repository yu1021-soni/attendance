<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

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
}
