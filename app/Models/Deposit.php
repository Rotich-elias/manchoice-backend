<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deposit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_id',
        'customer_id',
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
     * Get the loan that owns the deposit
     */
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    /**
     * Get the customer that owns the deposit
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who recorded this deposit
     */
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
