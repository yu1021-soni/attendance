<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function exportCsv(Request $request, $id)
    {
        // yearとmonthを取得（指定なしなら今の年月）
        $year  = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);

        // 指定の年・月の勤怠を取得
        $attendances = Attendance::with('user')
            ->where('user_id', $id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->orderBy('date')
            ->get();

        // タイトル行
        $csvHeader = [
            'ID',
            '名前',
            '日付',
            '出勤',
            '退勤',
            '休憩合計',
            '勤務時間',
        ];

        // CSV作成
        // php: //output -> 仮想ファイル
        // fputcsv() -> 配列を1行のCSVとして書き込む関数
        $callback = function () use ($attendances, $csvHeader) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, $csvHeader);

            // nullでもエラーにならないようにする
            foreach ($attendances as $attendance) {
                $row = [
                    $attendance->id,
                    $attendance->user->name,
                    $attendance->date->format('Y-m-d'),
                    optional($attendance->work_start)->format('H:i'),
                    optional($attendance->work_end)->format('H:i'),
                    $attendance->rest_total_human,
                    $attendance->work_time_human,
                ];

                // fputcsv($handle, $row) -> 1行ずつ CSV に書いていく
                fputcsv($handle, $row);
            }

            // 開いたファイルを閉じて後片付けする処理
            fclose($handle);
        };

        return response()->streamDownload(
            $callback,
            "{$year}-{$month}-attendances.csv",
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

}
