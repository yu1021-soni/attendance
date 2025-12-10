<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correction;
use App\Models\Attendance;
use App\Models\CorrectionRest;
use Illuminate\Support\Facades\DB;

class AdminApprovalController extends Controller
{
    public function index(Request $request)
    {
        // どのタブか（デフォルト pending）
        $tab = $request->query('tab', 'pending');

        // 基本クエリ（新しい順）
        $query = Correction::query()->latest();

        // タブに応じてステータスを絞り込み
        if ($tab === 'approved') {
            $query->where('status', Correction::STATUS_APPROVED);
        } else {
            $query->where('status', Correction::STATUS_PENDING);
        }

        $corrections = $query->get();

        return view('admin_application', [
            'corrections'  => $corrections,
            'tab'          => $tab,
            'searchParams' => $request->except('tab', 'page'),
        ]);
    }

    public function show($id)
    {
        // 対象出退勤記録を取得
        $attendance = Attendance::with('user', 'rests')->findOrFail($id);

        // 対象勤怠に紐づく最新の修正申請を1件取得
        $correction = Correction::where('attendance_id', $attendance->id)
            ->where('user_id', $attendance->user_id)
            ->latest()
            ->first();

        $status = $correction?->status;

        return view('approve', [
            'attendance' => $attendance,
            'status'     => $status,
            'correction' => $correction,
        ]);
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $attendance = Attendance::with('rests')->findOrFail($id);

            // 修正申請 + 休憩 + 紐づく勤怠を取得
            $correction = Correction::with('rests')
                ->where('attendance_id', $attendance->id)
                ->latest()
                ->firstOrFail();

            // すでに承認済みなら何もしない
            if ($correction->status === Correction::STATUS_APPROVED) {
                return;
            }

            $attendance = $correction->attendance;

            // 出勤・退勤を Attendance に反映
            if ($correction->new_work_start) {
                $attendance->work_start = $correction->new_work_start;
            }
            if ($correction->new_work_end) {
                $attendance->work_end = $correction->new_work_end;
            }

            // コメント反映（ユーザーコメント用）
            if (!is_null($correction->comment)) {
                $attendance->comment = $correction->comment;
            }

            // 既存の休憩を削除
            $attendance->rests()->delete();

            // 修正申請に紐づく休憩（CorrectionRest）を反映
            foreach ($correction->rests as $rest) {
                if ($rest->new_rest_start && $rest->new_rest_end) {

                    // 休憩時間（分）を計算して保存
                    $minutes = $rest->new_rest_start->diffInMinutes($rest->new_rest_end);

                    $attendance->rests()->create([
                        'rest_start'      => $rest->new_rest_start,
                        'rest_end'        => $rest->new_rest_end,
                        'rest_time_total' => $minutes,
                    ]);
                }
            }

            // ステータスを退勤済みに更新
            $attendance->status = Attendance::STATUS_DONE;

            // 勤怠保存
            $attendance->save();

            // 修正申請ステータスを承認に更新
            $correction->status = Correction::STATUS_APPROVED;
            $correction->approver_id = auth()->id(); // 承認した人のID（管理者）
            $correction->approved_at = now();
            $correction->save();

            // 休憩側のステータスも承認に更新
            CorrectionRest::where('correction_id', $correction->id)
                ->update([
                    'status' => CorrectionRest::STATUS_APPROVED,
                    'approver_id' => $correction->approver_id,
                    'approved_at' => $correction->approved_at,
                ]);
        });

        return back();
    }
}
