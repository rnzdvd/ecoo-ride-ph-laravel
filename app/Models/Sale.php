<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel convention
    protected $table = 'sales';

    // The attributes that are mass assignable
    protected $fillable = [
        'reference_id',
        'user_id',
        'amount',
        'status',
        'payment_method',
        'failure_reason',
    ];

    /**
     * Get the user who made the sale
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for paid sales
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope for failed or canceled sales
     */
    public function scopeFailedOrCanceled($query)
    {
        return $query->whereIn('status', ['failed', 'canceled']);
    }
}
