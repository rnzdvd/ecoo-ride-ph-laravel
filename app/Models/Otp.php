<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'time_sent',
        'expires_at',
    ];

    public $timestamps = true; // default, can omit
}
