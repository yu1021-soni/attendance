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
    public const STATUS_DONE  = 3; // 退勤済み

    protected $fillable = [
        'user_id',
        'date',
        'work_start',
        'work_end',
        'work_time_total',
        'user_comment',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'work_start' => 'datetime',
        'work_end' => 'datetime',
    ];

    public function calcWorkMinutes(): int {
        if (!$this->work_start || !$this->work_end) {
            return 0;
        }

        // 出勤～退勤のtotal時間(分)
        $total = $this->work_start->diffInMinutes($this->work_end);

        // 休憩合計（未終了の休憩は0分）
        $breakMinutes = $this->rests->sum(function ($r) {
            if (!$r->rest_start || !$r->rest_end) return 0;
            return $r->rest_start->diffInMinutes($r->rest_end);
        });

        return max(0, $total - $breakMinutes);
    }

    //表示用（例: 1時間30分）
    public function getWorkTimeHumanAttribute(): string {
        $minutes = (int) ($this->work_time_total ?? 0);
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $h > 0 ? "{$h}時間{$m}分" : "{$m}分";
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
