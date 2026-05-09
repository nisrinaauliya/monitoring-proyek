<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'dept_id',
        'role',
        'tim',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function ppsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'user_id');
    }

    public function histories()
    {
        return $this->hasMany(PpsmbHistory::class, 'pemeriksa');
    }

    //Role Relations
    public function projectLeaderPpsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'project_leader');
    }

    public function primaryBaPpsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'pic_ba');
    }

    public function secondaryBaPpsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'secondary_ba');
    }

    public function developerPpsmbs()
    {
        return $this->hasMany(Ppsmb::class, 'developer');
    }
}
