<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Optional if table name matches "cards")
     */
    protected $table = 'cards';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'payment_method_id',
        'brand',
        'last_4',
        'expiry_date',
        'cardholder_first_name',
        'cardholder_last_name',
        'cardholder_email',
        'cardholder_phone_number',
        'network',
        'type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     * (Optional, usually for sensitive fields)
     */
    protected $hidden = [
        // nothing sensitive here since we only store non-sensitive info
    ];

    /**
     * Define relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
