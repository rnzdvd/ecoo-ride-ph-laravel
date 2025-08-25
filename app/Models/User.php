<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'balance',
    ];

    // Required methods for JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function rides()
    {
        return $this->hasMany(Ride::class);
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
