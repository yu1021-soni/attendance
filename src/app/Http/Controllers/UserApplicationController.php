<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Correction;
use App\Models\CorrectionRest;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserApplicationController extends Controller
{
    ///correction-request/10 -> $id=10
    public function store($id) {
        // 対象出退勤記録を取得（ユーザー・休憩も一緒に取得）
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 対象勤怠に紐づく最新の修正申請を1件取得（休憩の修正も一緒に取得）
        $correction = Correction::with('rests')
            ->where('attendance_id', $attendance->id)
            //条件
            ->where('user_id', auth()->id())
            // 最新1件だけ取る
            ->latest()->first();

        $status    = $correction?->status;
        $isPending = $status === Correction::STATUS_PENDING;

        // Attendanceから取ってくる
        $beforeStart = $attendance->work_start?->format('H:i');
        $beforeEnd   = $attendance->work_end?->format('H:i');

        // Correctionから取ってくる
        $afterStart = $correction?->new_work_start?->format('H:i');
        $afterEnd   = $correction?->new_work_end?->format('H:i');

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction' => $correction,
            'isPending'  => $isPending,

            'beforeStart' => $beforeStart,
            'beforeEnd'   => $beforeEnd,
            'afterStart'  => $afterStart,
            'afterEnd'    => $afterEnd,
        ]);
    }

    public function show(CorrectionRequest $request)
    {
        //  元の勤怠データを取得（hidden で渡した id を使用）
        $attendance = Attendance::with('rests')->findOrFail($request->attendance_id);
        $date       = $attendance->date->format('Y-m-d');

        // 修正申請 (Correction) を保存
        $correction = new Correction();
        $correction->user_id        = auth()->id();
        $correction->attendance_id  = $attendance->id;
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;

        // 新しい出勤・退勤（入力があれば上書き、なければ null）
        $correction->new_work_start = $request->filled('work_start')
            ? Carbon::parse("$date {$request->work_start}")
            : null;

        $correction->new_work_end = $request->filled('work_end')
            ? Carbon::parse("$date {$request->work_end}")
            : null;

        $correction->comment = $request->comment;
        $correction->status  = Correction::STATUS_PENDING;
        $correction->save();

        // 休憩の修正を CorrectionRest に保存
        $restsInput = $request->input('rests', []);

        foreach ($restsInput as $rest) {
            // 完全に空の行はスキップ
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            $restModel = new CorrectionRest();
            $restModel->correction_id = $correction->id;

            // 既存の休憩行があれば、その old 値を取得（IDで紐づけ）
            $oldRest = !empty($rest['id'])
                ? $attendance->rests->firstWhere('id', $rest['id'])
                : null;

            $restModel->old_rest_start = $oldRest?->rest_start;
            $restModel->old_rest_end   = $oldRest?->rest_end;

            // 今回入力された new 値を保存
            if (!empty($rest['rest_start'])) {
                $restModel->new_rest_start = Carbon::parse("$date {$rest['rest_start']}");
            }
            if (!empty($rest['rest_end'])) {
                $restModel->new_rest_end = Carbon::parse("$date {$rest['rest_end']}");
            }

            $restModel->status = CorrectionRest::STATUS_PENDING;
            $restModel->save();
        }

        return redirect()->route('correction.store', ['id' => $attendance->id]);
    }

    public function createNew(Request $request) {
        $user = $request->user();
        // URL のクエリ (?date=) から日付を取得
        $date = $request->query('date');

        // 仮の Attendance（DBにはまだ保存しない）
        //$attendance = new Attendance();
        //$attendance->date    = $date;
        //$attendance->user_id = auth()->id();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance) {
            return redirect()->route('correction.store', ['id' => $attendance->id]);
        }

        $attendance = new Attendance();
        $attendance->user_id = $user->id;
        $attendance->date    = $date;

        // まだ申請していないからstatus は null
        $status    = null;
        $isPending = false;
        $correction  = null;
        $beforeStart = null;
        $beforeEnd   = null;
        $afterStart  = null;
        $afterEnd    = null;

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction'   => $correction,
            'isPending'  => $isPending,
            'beforeStart'  => $beforeStart,
            'beforeEnd'    => $beforeEnd,
            'afterStart'   => $afterStart,
            'afterEnd'     => $afterEnd,
        ]);
    }

    public function newStore(CorrectionRequest $request) {
        $user = $request->user();
        $date = $request->input('date');

        // 日付と時間を一緒に並べる（よく使う書き方らしい）
        $workStart = Carbon::parse("$date {$request->input('work_start')}");
        $workEnd   = Carbon::parse("$date {$request->input('work_end')}");

        // 勤怠データを作成取得
        // firstOrCreate()
        // ① 条件に一致するレコードがあれば取得
        // ② なければ新しく作って保存して返す
        $attendance = Attendance::firstOrCreate(
            // 一致するレコードを探す
            [
                'user_id' => $user->id,
                'date'    => $date,
            ],
            // レコードがなかった場合に作成
            [
                'work_start'   => $workStart,
                'work_end'     => $workEnd,
                'status'     => Attendance::STATUS_CORRECTION_PENDING,
                'comment'      => null,
            ]
        );

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $date]
        );

        if (
            Correction::where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('status', Correction::STATUS_PENDING)
            ->exists()
        ) {
            return redirect()->route('correction.store', ['id' => $attendance->id]);
        }

        // 修正申請作成
        $correction = new Correction();
        $correction->user_id        = $user->id;
        $correction->attendance_id  = $attendance->id;
        $correction->old_work_start = null;
        $correction->old_work_end   = null;
        $correction->new_work_start = $workStart;
        $correction->new_work_end   = $workEnd;
        $correction->comment        = $request->comment;
        $correction->status         = Correction::STATUS_PENDING;
        $correction->save();

        // 休憩（複数）を保存
        $restsInput = $request->input('rests', []);

        foreach ($restsInput as $rest) {
            // 入力が空の行はスキップ
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            $restModel = new CorrectionRest();
            $restModel->correction_id  = $correction->id;

            // 新規なので old はすべて null
            $restModel->old_rest_start = null;
            $restModel->old_rest_end   = null;

            // new 値を保存
            if (!empty($rest['rest_start'])) {
                $restModel->new_rest_start = Carbon::parse("$date {$rest['rest_start']}");
            }
            if (!empty($rest['rest_end'])) {
                $restModel->new_rest_end = Carbon::parse("$date {$rest['rest_end']}");
            }

            $restModel->status = CorrectionRest::STATUS_PENDING;
            $restModel->save();
        }

        return redirect()->route('correction.store', ['id' => $attendance->id]);
    }

    public function create(Request $request) {
        // どのタブか（デフォルト pending）
        $tab = $request->query('tab', 'pending');

        // 基本クエリ（新しい順）
        $query = Correction::with(['attendance', 'user'])
            ->where('user_id', Auth::id());

        // タブに応じてステータスを絞り込み
        if ($tab === 'approved') {
            $query->where('status', Correction::STATUS_APPROVED);
        } else {
            $query->where('status', Correction::STATUS_PENDING);
        }

        $corrections = $query->get();

        return view('user_application', [
            'corrections'  => $corrections,
            'tab'          => $tab,
            'searchParams' => $request->except('tab', 'page'),
        ]);
    }
}
