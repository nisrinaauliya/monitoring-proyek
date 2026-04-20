<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpsmbHistory extends Model
{
    protected $fillable = [
        'ppsmb_id',
        'pemeriksa',
        'status',
        'catatan',
    ];

    public function ppsmb()
    {
        return $this->belongsTo(Ppsmb::class);
    }
}
