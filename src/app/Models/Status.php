<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    //const PENDING = 1;
    //const APPROVED = 2;

    protected $fillable = [
        'correction_id',
        'status'
    ];

    public function correction() {
        return $this->belongsTo(Status::class);
    }
}
