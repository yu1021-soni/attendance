<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorrectionRest extends Model
{
    use HasFactory;

    protected $fillable = [
        'correction_id',
        'new_rest_start_1',
        'new_rest_end_1',
        'new_rest_start_2',
        'new_rest_end_2',
    ];

    protected $casts = [
        'new_rest_start_1' => 'datetime',
        'new_rest_end_1'   => 'datetime',
        'new_rest_start_2' => 'datetime',
        'new_rest_end_2'   => 'datetime',
    ];

    public function collection() {
        return $this->belongsTo(Correction::class);
    }
}
