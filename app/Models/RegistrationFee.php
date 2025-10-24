<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationFee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'amount',
        'transaction_id',
        'mpesa_receipt_number',
        'phone_number',
        'payment_method',
        'status',
        'paid_at',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the registration fee
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who recorded this fee
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
