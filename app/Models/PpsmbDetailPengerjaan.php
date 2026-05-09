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
        'is_done',
    ];

    protected $casts = [
        'mandays' => 'decimal:2',
        'adjustment_mandays' => 'decimal:2',
        'is_done' => 'boolean',
    ];
    
    public function ppsmb()
    {
        return $this->belongsTo(Ppsmb::class, 'ppsmb_id');
    }
}
