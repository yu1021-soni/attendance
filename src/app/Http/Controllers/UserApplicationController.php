<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Correction;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;

class UserApplicationController extends Controller
{
    ///correction-request/10 -> $id=10
    public function store($id) {

        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請があるかどうか
        $correction = Correction::where('attendance_id', $attendance->id)
            //条件
            ->where('user_id', auth()->id())
            // 最新1件だけ取る
            ->latest()->first();


        $status = $correction?->status;

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status' => $status,
        ]);
    }


    public function show(CorrectionRequest $request) {
        // 1. 元の勤怠データを取得（hidden で渡した id を使う想定）
        $attendance = Attendance::findOrFail($request->attendance_id);

        // 2. 修正申請を作成
        $correction = new Correction;
        $correction->user_id = auth()->id();
        $correction->attendance_id = $attendance->id;

        // 古い値（old）
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end = $attendance->work_end;

        // 新しい値（new）
        $correction->new_work_start = $request->work_start;
        $correction->new_work_end = $request->work_end;

        // 備考
        $correction->comment = $request->comment;

        // ステータスは必ず pending
        $correction->status = Correction::STATUS_PENDING;

        $correction->save();

        // 3. 承認待ち画面へ
        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status' => $correction->status,
        ]);
    }

    public function createNew(Request $request) {
        //query()   URL の「?」以降を見る
        $date = $request->query('date');

        // 仮の勤怠データ
        $attendance = new Attendance(); // 空のattendance1件
        $attendance->date = $date; // 今日に日付
        $attendance->user_id = auth()->id(); // ログインユーザ

        // まだ出勤していないからnull
        $status = null;

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status' => $status,
        ]);
    }

    public function newStore(CorrectionRequest $request) {

        $user = $request->user(); // ログインユーザ
        $date = $request->input('date');

        // 入力された時刻
        $workStartTime = $request->input('work_start');
        $workEndTime = $request->input('work_end');

        // 日付と時間を一緒に並べる（よく使う書き方らしい）
        $workStart = Carbon::parse("$date $workStartTime"); // 2025-11-14 09:00:00
        $workEnd = Carbon::parse("$date $workEndTime");   // 2025-11-14 18:00:00

        // 勤怠データを作成取得
        // firstOrCreate()
        // ① 条件に一致するレコードがあれば取得
        // ② なければ新しく作って保存して返す
        $attendance = Attendance::firstOrCreate(
            // 一致するレコードを探す
            [
                'user_id' => $user->id,
                'date' => $date,
            ],
            // レコードがなかった場合に作成
            [
                'work_start' => $workStart,
                'work_end' => $workEnd,
                'status' => Attendance::STATUS_DONE,
                'user_comment' => null,
            ]
        );

        // 修正申請作成
        $correction = new Correction();
        $correction->user_id = $user->id;
        $correction->attendance_id = $attendance->id;

        // 修正前（old）
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end = $attendance->work_end;

        // 修正後（new）
        $correction->new_work_start = $workStart;
        $correction->new_work_end = $workEnd;

        // 備考
        $correction->comment = $request->comment;
        $correction->status = Correction::STATUS_PENDING;

        $correction->save();

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status' => $correction->status,
        ]);
    }

    public function create(Request $request)
    {
        // どのタブか（?tab=approved なら approved、それ以外は pending 扱い）
        $tab = $request->query('tab', 'pending');

        // 基本クエリ（新しい順）
        $query = Correction::query()->latest();

        // タブに応じて status を数字で絞る
        if ($tab === 'approved') {
            // 承認済みタブ
            $query->where('status', Correction::STATUS_APPROVED);
        } else {
            // デフォルト：承認待ちタブ
            $query->where('status', Correction::STATUS_PENDING);
        }

        // 実行
        $corrections = $query->get();

        // 画面へ渡す
        return view('user_application', [
            'corrections'  => $corrections,
            'tab'          => $tab,
            'searchParams' => $request->except('tab', 'page'),
        ]);
    }
}
