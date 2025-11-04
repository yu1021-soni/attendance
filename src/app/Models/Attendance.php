<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'work_start',
        'work_end',
        'work_time_total',
        'comment',
    ];

    protected $casts = [
        'date' => 'date',
        'work_start' => 'datetime',
        'work_end' => 'datetime',
    ];

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
