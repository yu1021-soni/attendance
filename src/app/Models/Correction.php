<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'new_work_start',
        'new_work_end',
        'comment',
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
}
