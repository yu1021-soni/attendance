<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffController extends Controller
{
    public function index() {

        // 管理者除外
        $users = User::where('role', '!=', 1)->get();

        return view('staff_list',['users' => $users]);
    }

    public function show(Request $request,$id) {

        // 一般ユーザID
        $user = User::findOrFail($id);

        // 表示する年月
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // 前月次月移動
        $move = $request->query('move');
        if ($move === 'prev') $month--;
        if ($move === 'next') $month++;

        if ($month === 0) {
            $month = 12;
            $year--;
        }
        if ($month === 13) {
            $month = 1;
            $year++;
        }

        // 月初月末
        $monthStart = Carbon::create($year, $month, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        // 一般ユーザーの 月の勤怠一覧を取得
        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->orderBy('date')
            ->get();

        return view('staff_detail', [
            'user' => $user,
            'attendances' => $attendances,
            'year' => $year,
            'month' => $month,
        ]);
    }
}
