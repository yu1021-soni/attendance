<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    use HasFactory;

    const STATUS_PENDING  = 1; // 申請中
    const STATUS_APPROVED = 2; // 承認済み

    protected $fillable = [
        'user_id',
        'attendance_id',
        'old_work_start',
        'old_work_end',
        'new_work_start',
        'new_work_end',
        'comment',
        'status',
        'approver_id',
        'approved_at',
    ];

    protected $casts = [
        'old_work_start' => 'datetime',
        'old_work_end' => 'datetime',
        'new_work_start' => 'datetime',
        'new_work_end' => 'datetime',
        'approved_at' => 'datetime',
        'status' => 'integer',
    ];


    public function attendance() {
        return $this->belongsTo(Attendance::class);
    }

    public function correctionRest() {
        return $this->hasOne(CorrectionRest::class);
    }

    public function status() {
        return $this->hasOne(Status::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
