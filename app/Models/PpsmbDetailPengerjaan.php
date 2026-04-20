<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PpsmbDetailPengerjaan extends Model
{
    protected $fillable = [
        'ppsmb_id',
        'menu',
        'penilaian',
        'mandays',
        'adjustment_mandays',
    ];

    public function ppsmb()
    {
        return $this->belongsTo(Ppsmb::class);
    }
}
