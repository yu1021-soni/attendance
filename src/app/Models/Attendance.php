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
    public const STATUS_CORRECTION_PENDING = 4; // 修正申請中（承認待ち）

    protected $fillable = [
        'user_id',
        'date',
        'work_start',
        'work_end',
        'comment',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'work_start' => 'datetime',
        'work_end' => 'datetime',
        'status' => 'integer',
    ];

    private function cutSeconds($dt) {
        return $dt ? $dt->copy()->startOfMinute() : null;
    }

    public function calcWorkMinutes(): int {

        // $this ＝ $attendance

        // !->否定  ||->または
        if (!$this->work_start || !$this->work_end) {
            return 0;
        }

        // 秒は見ない
        $start = $this->cutSeconds($this->work_start);
        $end   = $this->cutSeconds($this->work_end);

        $total = $start->diffInMinutes($end);

        $breakMinutes = 0;

        // rests（休憩）を 1つずつ取り出す
        foreach ($this->rests as $rest) {

            // 休憩の開始・終了が無い場合はスキップ
            if (!$rest->rest_start || !$rest->rest_end) {
                continue;
            }

            // 休憩時間（分）を計算して足す
            $rs = $this->cutSeconds($rest->rest_start); // 休憩開始
            $re = $this->cutSeconds($rest->rest_end);   // 休憩終了

            // 休憩開始から終了までの分数
            $breakMinutes += $rs->diffInMinutes($re);
        }

        // $total は出勤〜退勤の全体の分数
        // $breakMinutes は休憩の合計
        // → この差が 勤務時間（実働時間）
        return max(0, $total - $breakMinutes);
    }

    public function getWorkTimeTotalAttribute(): int {
        // =$attendance->work_time_total
        return $this->calcWorkMinutes();
    }

    // 勤務時間の合計（H:MM）
    public function getWorkTimeHumanAttribute(): string
    {
        // $this 1件の勤怠データ（Attendance）
        // 勤務時間（分）
        $total = (int) $this->work_time_total;

        // 勤務時間が 0分以下ならnull
        if ($total <= 0) return '';

        // intdiv() 整数の割り算
        $hour = intdiv($total, 60);
        //60分で割った残りの分
        $minutes = $total % 60;

        // %d:%02d  %d=整数  %02d=整数を2桁で表示,1桁なら先頭に0
        return sprintf('%d:%02d', $hour, $minutes);
    }

    // 休憩合計時間
    public function getRestTotalMinutesAttribute(): int
    {
        $total = 0;

        foreach ($this->rests as $rest) {
            if (!$rest->rest_start || !$rest->rest_end) continue;

            $rs = $this->cutSeconds($rest->rest_start);
            $re = $this->cutSeconds($rest->rest_end);

            $total += $rs->diffInMinutes($re);
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
