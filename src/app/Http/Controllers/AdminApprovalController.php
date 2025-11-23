<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Correction;

class AdminApprovalController extends Controller
{
    public function index(Request $request) {

        // どのタブか（デフォルト pending）
        //query() URLの?以降（クエリパラメータ）を読むための機能
        $tab = $request->query('tab', 'pending');

        // 基本クエリ（新しい順）
        $query = Correction::query()->latest();

        // タブに応じてステータスを絞り込み
        // URLが ?tab=approved の時
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

    public function show() {

    }

    public function approval() {

    }
}
