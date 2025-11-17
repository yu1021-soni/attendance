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


    public function show(CorrectionRequest $request)
    {
        // 1. 元の勤怠データを取得（hidden で渡した id を使う想定）
        $attendance = Attendance::findOrFail($request->attendance_id);

        // 2. 修正申請を作成
        $correction = new Correction;
        $correction->user_id       = auth()->id();
        $correction->attendance_id = $attendance->id;

        // 古い値（old）
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;

        // 新しい値（new）
        $correction->new_work_start = $request->work_start;
        $correction->new_work_end   = $request->work_end;

        // 備考
        $correction->comment = $request->comment;

        // ステータスは必ず pending
        $correction->status = Correction::STATUS_PENDING;

        $correction->save();

        // 3. 承認待ち画面へ
        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $correction->status,
        ]);
    }

    public function createNew(Request $request)
    {
        $date = $request->query('date');

        // ★空のダミーAttendanceモデル（DB保存しない）
        $attendance = new Attendance();
        $attendance->date = $date;
        $attendance->user_id = auth()->id();

        // status も「0 = 未出勤」としておく
        $status = null; // 修正申請がまだ存在しないので

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
        ]);
    }

    public function newStore(CorrectionRequest $request)
    {
        // ① CorrectionRequest が基本バリデーション（日付・時刻形式・休憩チェックなど）を実施

        $user = $request->user();
        $date = $request->input('date');

        // 入力された「時刻」（H:i）を、その日の日時にくっつける
        $workStartTime = $request->input('work_start'); // 例: 09:00
        $workEndTime   = $request->input('work_end');   // 例: 18:00

        $workStart = Carbon::parse("$date $workStartTime"); // 2025-11-14 09:00:00
        $workEnd   = Carbon::parse("$date $workEndTime");   // 2025-11-14 18:00:00

        // ③ その日の Attendance を作成 or 取得
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ],
            [
                'work_start'   => $workStart,
                'work_end'     => $workEnd,
                'status'       => Attendance::STATUS_DONE, // or STATUS_OFF でもOK
                'user_comment' => null,
            ]
        );

        // ④ 修正申請（Correction）も作る（既存のままでOK）
        $correction = new Correction();
        $correction->user_id       = $user->id;
        $correction->attendance_id = $attendance->id;

        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;

        $correction->new_work_start = $workStart;
        $correction->new_work_end   = $workEnd;

        // 備考（こちらも name="comment" を使う想定）
        $correction->comment = $request->comment;
        $correction->status  = Correction::STATUS_PENDING;

        $correction->save();

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $correction->status,
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
