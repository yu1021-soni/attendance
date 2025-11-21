<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Correction;
use App\Models\CorrectionRest;
use App\Http\Requests\CorrectionRequest;
use Carbon\Carbon;

class UserApplicationController extends Controller
{
    ///correction-request/10 -> $id=10
    public function store($id)
    {

        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請 + 休憩修正も一緒に取得
        $correction = Correction::with('rests')
            ->where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        $status = $correction?->status;

        return view('user_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction' => $correction,   // ★ 追加
        ]);
    }



    public function show(CorrectionRequest $request) {
        $attendance = Attendance::with('rests')->findOrFail($request->attendance_id);
        $date = $attendance->date->format('Y-m-d');

        // Correction 作成
        $correction = new Correction();
        $correction->user_id        = auth()->id();
        $correction->attendance_id  = $attendance->id;
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;
        $correction->new_work_start = $request->filled('work_start')
            ? Carbon::parse("$date {$request->work_start}")
            : null;
        $correction->new_work_end = $request->filled('work_end')
            ? Carbon::parse("$date {$request->work_end}")
            : null;
        $correction->comment        = $request->comment;
        $correction->status         = Correction::STATUS_PENDING;
        $correction->save();

        // 修正された休憩を CorrectionRest に保存
        foreach ($request->input('rests', []) as $rest) {

            // 空行はスキップ
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            $restModel = new CorrectionRest();
            $restModel->correction_id = $correction->id;

            /**
             * ★ old の取得法をID方式に変更（重要）
             * これで old_rest_start / old_rest_end が正確に取れる
             */
            $oldRest = !empty($rest['id'])
                ? $attendance->rests->firstWhere('id', $rest['id'])
                : null;

            $restModel->old_rest_start = $oldRest?->rest_start;
            $restModel->old_rest_end   = $oldRest?->rest_end;

            // new（今回の入力）
            if (!empty($rest['rest_start'])) {
                $restModel->new_rest_start = Carbon::parse("$date {$rest['rest_start']}");
            }
            if (!empty($rest['rest_end'])) {
                $restModel->new_rest_end   = Carbon::parse("$date {$rest['rest_end']}");
            }

            $restModel->status = CorrectionRest::STATUS_PENDING;
            $restModel->save();
        }

        return redirect()->route('correction.store', ['id' => $attendance->id]);
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

        $user = $request->user();
        $date = $request->input('date');

        // 出勤退勤
        $workStart = Carbon::parse("$date {$request->input('work_start')}");
        $workEnd   = Carbon::parse("$date {$request->input('work_end')}");

        // 勤怠作成または取得
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ],
            [
                'work_start'   => $workStart,
                'work_end'     => $workEnd,
                'status'       => Attendance::STATUS_DONE,
                'user_comment' => null,
            ]
        );

        // Correction 作成
        $correction = new Correction();
        $correction->user_id        = $user->id;
        $correction->attendance_id  = $attendance->id;
        $correction->old_work_start = $attendance->work_start;
        $correction->old_work_end   = $attendance->work_end;
        $correction->new_work_start = $workStart;
        $correction->new_work_end   = $workEnd;
        $correction->comment        = $request->comment;
        $correction->status         = Correction::STATUS_PENDING;
        $correction->save();

        // 休憩（複数）
        $rests = $request->input('rests', []);

        foreach ($rests as $rest) {

            // 入力が空の行はスキップ
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            $restModel = new CorrectionRest();
            $restModel->correction_id  = $correction->id;

            // 新規なので old は全部 null
            $restModel->old_rest_start = null;
            $restModel->old_rest_end   = null;

            // new を保存
            if (!empty($rest['rest_start'])) {
                $restModel->new_rest_start = Carbon::parse("$date {$rest['rest_start']}");
            }
            if (!empty($rest['rest_end'])) {
                $restModel->new_rest_end   = Carbon::parse("$date {$rest['rest_end']}");
            }

            $restModel->status = CorrectionRest::STATUS_PENDING;
            $restModel->save();
        }

        return redirect()->route('correction.store', ['id' => $attendance->id]);
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
