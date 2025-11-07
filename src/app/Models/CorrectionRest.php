<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_id',
        'old_rest_start',
        'old_rest_end',
        'new_rest_start',
        'new_rest_end',
        'status',
        'approver_id',
        'approved_at',
    ];

    protected $casts = [
        'old_rest_start' => 'datetime',
        'old_rest_end'   => 'datetime',
        'new_rest_start' => 'datetime',
        'new_rest_end'   => 'datetime',
        'approved_at'    => 'datetime',
        'status'         => 'integer',
    ];

    public function correction() {
        return $this->belongsTo(Correction::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
