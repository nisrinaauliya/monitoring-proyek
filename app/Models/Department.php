<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'dept_id');
    }

    public function ppsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'dept_id');
    }
}
