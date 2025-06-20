<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class parents extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'parents';
    protected $fillable = [
        'name',
        'email',
        'email_code',
        'email_verified_at',
        'password',
        'phone',
        'phone_code',
        'phone_verified_at',
        'address',
        'age',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function students()
    {
        return $this->hasMany(student::class);
    }

    public function notification()
    {
        return $this->hasOne(notification::class);
    }
}
