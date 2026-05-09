<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpsmbHistory extends Model
{
    protected $fillable = [
        'ppsmb_id',
        'pemeriksa',
        'status',
        'progress',
        'catatan',
    ];

    protected $casts = [
        'progress' => 'decimal:2',
    ];

    public function ppsmb()
    {
        return $this->belongsTo(Ppsmb::class, 'ppsmb_id');
    }

    public function pemeriksaUser()
    {
        return $this->belongsTo(User::class, 'pemeriksa');
    }
}
