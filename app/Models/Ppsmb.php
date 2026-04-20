<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ppsmb extends Model
{
    protected $fillable = [
        'no_ppsmb',
        'user_id',
        'dept',
        'model_aplikasi',
        'nama_project',
        'tahun',
        'quartal',
        'jenis_permintaan',
        'uraian_permintaan',
        'project_leader',
        'pic_ba',
        'secondary_ba',
        'tangible_benefit',
        'intangible_benefit',
        'file',
        'status',
        'progress',
        'estimasi_mulai',
        'estimasi_selesai',
        'revisi_at',
    ];

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke history
    public function histories()
    {
        return $this->hasMany(PpsmbHistory::class);
    }

    // Relasi ke detail pengerjaan
    public function detailPengerjaan()
    {
        return $this->hasMany(PpsmbDetailPengerjaan::class);
    }
}
