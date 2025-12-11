<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Correction;
use App\Http\Requests\CorrectionRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 年月日を取得（指定なければ今日）
        $year  = (int) $request->query('year',  now()->year);
        $month = (int) $request->query('month', now()->month);
        $day   = (int) $request->query('day',   now()->day);

        // 日付作成
        $date = Carbon::create($year, $month, $day);

        // 前日・翌日の移動
        $move = $request->query('move');
        if ($move === 'prev') {
            $date->subDay();
        } elseif ($move === 'next') {
            $date->addDay();
        }

        // 画面用に年月日を上書き
        $year  = $date->year;
        $month = $date->month;
        $day   = $date->day;

        // 勤怠データ取得（ユーザー・休憩込み）
        $attendances = Attendance::with(['user', 'rests'])
            ->whereDate('date', $date->toDateString())
            ->whereDoesntHave('corrections', function ($approval) {
                $approval->where('status', Correction::STATUS_PENDING);
            })
            ->get();

        return view('admin_attendance_list', compact('year', 'month', 'day', 'attendances'));
    }

    public function show($id)
    {
        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 修正申請があるかどうか
        $correction = Correction::where('attendance_id', $attendance->id)
            ->where('user_id', $attendance->user_id)
            ->latest()
            ->first();

        $status = $correction?->status;

        return view('admin_attendance_show', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction' => $correction,
        ]);
    }

    public function updateCorrection(CorrectionRequest $request, $id)
    {
        // 対象の勤怠を取得（休憩も一緒に）
        $attendance = Attendance::with('rests')->findOrFail($id);

        // CorrectionRequest でバリデーション済みの値を取得
        $validated = $request->validated();

        // 日付を文字列で用意
        $dateStr = $attendance->date->format('Y-m-d');

        // 出勤・退勤を更新
        if (!empty($validated['work_start'])) {
            $attendance->work_start = Carbon::parse($dateStr . ' ' . $validated['work_start']);
        }

        if (!empty($validated['work_end'])) {
            $attendance->work_end = Carbon::parse($dateStr . ' ' . $validated['work_end']);
        }

        // 備考を更新
        if (array_key_exists('comment', $validated)) {
            $attendance->comment = $validated['comment'];
        }

        // 休憩の更新
        // フォームから送られてきた休憩配列
        $restsInput = $request->input('rests', []);

        foreach ($restsInput as $restData) {
            $restId    = $restData['id']         ?? null;
            $restStart = $restData['rest_start'] ?? null;
            $restEnd   = $restData['rest_end']   ?? null;

            // 既存のRestを探す
            $restModel = $restId
                ? $attendance->rests->firstWhere('id', $restId)
                : null;

            // 開始・終了どちらも空なら：
            // 既存行があれば削除してスキップ
            if (empty($restStart) && empty($restEnd)) {
                if ($restModel) {
                    $restModel->delete();
                }
                continue;
            }

            // 既存レコードがなければ新しく作成
            if (!$restModel) {
                $restModel = $attendance->rests()->make();
            }

            // rest_start / rest_end を日時で保存
            $restModel->rest_start = $restStart
                ? Carbon::parse($dateStr . ' ' . $restStart)
                : null;

            $restModel->rest_end = $restEnd
                ? Carbon::parse($dateStr . ' ' . $restEnd)
                : null;

            $restModel->save();
        }

        // 勤怠本体を保存
        $attendance->save();

        return redirect()
            ->route('admin.show', $attendance->id)
            ->with('success', '修正を反映しました');
    }

    public function createNew(Request $request)
    {
        // 「対象スタッフ」の ID をクエリからもらう
        $targetUserId = $request->query('user_id');
        $user = User::findOrFail($targetUserId);

        // ?date= から日付を取得
        $date = $request->query('date');

        // すでにその日の勤怠があれば、通常の修正画面に飛ばす
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance) {
            // すでに勤怠がある日は管理者の詳細画面へ
            return redirect()->route('admin.show', ['id' => $attendance->id]);
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

        return view('admin_attendance_show', [
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

    public function newStore(CorrectionRequest $request)
    {
        // 管理者からの新規登録用
        // hidden の user_id で対象スタッフを特定
        $user = User::findOrFail($request->input('user_id'));
        $date = $request->input('date');

        // 日付と時間を一緒に並べる
        $workStartInput = $request->input('work_start');
        $workEndInput   = $request->input('work_end');

        $workStart = $workStartInput
            ? Carbon::parse("$date {$workStartInput}")
            : null;

        $workEnd = $workEndInput
            ? Carbon::parse("$date {$workEndInput}")
            : null;

        // 対象日の勤怠を作成 or 取得
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'date'    => $date,
            ],
            [
                'work_start' => $workStart,
                'work_end'   => $workEnd,
                // プロジェクトの定義に合わせて（例：3=退勤済）
                'status'     =>  3,
                'comment'    => $request->input('comment'),
            ]
        );

        // すでにあったレコードなら上書き（
        if (! $attendance->wasRecentlyCreated) {
            $attendance->work_start = $workStart;
            $attendance->work_end   = $workEnd;
            $attendance->comment    = $request->input('comment');
            $attendance->status     =  3;
            $attendance->save();
        }

        // 休憩（複数）を保存
        $restsInput = $request->input('rests', []);

        // いったんその日の休憩を全部消す
        $attendance->rests()->delete();

        // 新しく入力された休憩で作り直す
        foreach ($restsInput as $rest) {
            if (empty($rest['rest_start']) && empty($rest['rest_end'])) {
                continue;
            }

            $restStart = !empty($rest['rest_start'])
                ? Carbon::parse("$date {$rest['rest_start']}")
                : null;

            $restEnd = !empty($rest['rest_end'])
                ? Carbon::parse("$date {$rest['rest_end']}")
                : null;

            $attendance->rests()->create([
                'rest_start' => $restStart,
                'rest_end'   => $restEnd,
            ]);
        }

        return redirect()
            ->route('admin.show', ['id' => $attendance->id])
            ->with('success', '修正を反映しました');
    }

    public function create(Request $request)
    {
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