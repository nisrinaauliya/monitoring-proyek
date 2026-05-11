<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ppsmb extends Model
{
    protected $fillable = [
        'no_ppsmb',
        'user_id',
        'dept_id',
        'model_aplikasi',
        'nama_project',
        'tahun',
        'quartal',
        'jenis_permintaan',
        'uraian_permintaan',

        'project_leader',
        'pic_ba',
        'secondary_ba',
        'developer',

        'tangible_benefit',
        'intangible_benefit',

        'file',

        'status',
        'progress',

        'estimasi_mulai',
        'estimasi_selesai',

        'revisi_at',
    ];

    protected $casts = [
        'tangible_benefit' => 'decimal:2',
        'progress' => 'decimal:2',

        'estimasi_mulai' => 'date',
        'estimasi_selesai' => 'date',

        'revisi_at' => 'datetime',
    ];

    public function hitungProgress(): float
    {
        $this->load('detailPengerjaan');
        $total = $this->detailPengerjaan->sum('mandays');
        $done  = $this->detailPengerjaan->where('is_done', true)->sum('mandays');
        return $total > 0 ? round(($done / $total) * 90, 2) : 0;
    }

    public function getAgingHariAttribute(): ?int
    {
        $latest = $this->histories->sortByDesc('created_at')->first();
        return $latest
            ? (int) Carbon::parse($latest->created_at)->diffInDays(now())
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function projectLeader()
    {
        return $this->belongsTo(User::class, 'project_leader');
    }

    public function picBa()
    {
        return $this->belongsTo(User::class, 'pic_ba');
    }

    public function secondaryBa()
    {
        return $this->belongsTo(User::class, 'secondary_ba');
    }

    public function developerUser()
    {
        return $this->belongsTo(User::class, 'developer');
    }

    public function histories()
    {
        return $this->hasMany(PpsmbHistory::class);
    }

    public function detailPengerjaan()
    {
        return $this->hasMany(PpsmbDetailPengerjaan::class);
    }

    public function getEstimasiMulaiFormattedAttribute()
    {
        return $this->estimasi_mulai?->translatedFormat('d F Y') ?? '-';
    }

    public function getEstimasiSelesaiFormattedAttribute()
    {
        return $this->estimasi_selesai?->translatedFormat('d F Y') ?? '-';
    }
}
