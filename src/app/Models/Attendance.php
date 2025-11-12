<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_OFF   = 0; // 未出勤
    public const STATUS_ON    = 1; // 出勤中
    public const STATUS_BREAK = 2; // 休憩中
    public const STATUS_DONE  = 3; // 退勤済

    protected $fillable = [
        'user_id',
        'date',
        'work_start',
        'work_end',
        'user_comment',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'work_start' => 'datetime',
        'work_end' => 'datetime',
        'status' => 'integer',
    ];

    public function calcWorkMinutes(): int {

        // $this ＝ $attendance

        // !->否定  ||->または
        if (!$this->work_start || !$this->work_end) {
            return 0;
        }

        // 出勤～退勤のtotal時間(分)
        // diffInMinutes->総時間を分で取得
        $total = $this->work_start->diffInMinutes($this->work_end);

        $breakMinutes = 0;

        // rests（休憩）を 1つずつ取り出す
        foreach ($this->rests as $rest) {

            // 休憩の開始・終了が無い場合はスキップ
            if (!$rest->rest_start || !$rest->rest_end) {
                continue;
            }

            // 休憩時間（分）を計算して足す
            $start = $rest->rest_start; // 休憩開始
            $end = $rest->rest_end;   // 休憩終了

            $diff = $start->diffInMinutes($end); // 開始から終了までの分数

            //左側$breakMinutesは上書き
            $breakMinutes = $breakMinutes + $diff; // 合計時間に足す
        }

        return max(0, $total - $breakMinutes);
    }

    /** ★仮想属性：DBに無くても $attendance->work_time_total が使える */
    public function getWorkTimeTotalAttribute(): int
    {
        return $this->calcWorkMinutes();
    }

    //表示用（例: 1時間30分）
    //public function getWorkTimeHumanAttribute(): string {

    //条件式 ? 条件がtrueのときの値 : falseのときの値;
    // 分を取り出す（null の場合は 0）
    //$minutes = $this->work_time_total ? $this->work_time_total : 0;

    // 分 → 時間 と 残りの分 に変換
    //$hours = floor($minutes / 60);   // 60で割った時間
    //$mins  = $minutes % 60;          // 余った分

    // 表示の形式
    //if ($hours > 0) {
    //return $hours . "時間" . $mins . "分";
    //} else {
    //return $mins . "分";
    //}
    //}

    // 勤務時間の合計（H:MM）
    public function getWorkTimeHumanAttribute(): string
    {
        $total = (int) $this->work_time_total;

        if ($total <= 0) return '';

        $h = intdiv($total, 60);
        $m = $total % 60;

        return sprintf('%d:%02d', $h, $m);
    }

    public function getRestTotalMinutesAttribute(): int
    {
        $total = 0;

        foreach ($this->rests as $rest) {
            if (!is_null($rest->rest_time_total)) {
                $total += (int) $rest->rest_time_total;
            } elseif ($rest->rest_start && $rest->rest_end) {
                $total += $rest->rest_start->diffInMinutes($rest->rest_end);
            }
        }

        return $total;
    }

    public function getRestTotalHumanAttribute(): string
    {
        $m = $this->rest_total_minutes;

        if ($m <= 0) return '';

        return sprintf('%d:%02d', intdiv($m, 60), $m % 60);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function rests() {
        return $this->hasMany(Rest::class);
    }

    public function corrections() {
        return $this->hasMany(Correction::class);
    }
}
