<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Ride extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    // The attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'scooter_id',
        'started_at',
        'ended_at',
        'status',
        'last_billed_at',
        'billed_intervals',
        'end_reason',
        'option',
        'total_distance',
        'total_charged',
        'curr_lat',
        'curr_lng',
    ];

    // Dates to be cast to Carbon instances
    protected $dates = [
        'started_at',
        'ended_at',
        'last_billed_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
