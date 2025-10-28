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
        'type',
        'transaction_id',
        'mpesa_receipt_number',
        'phone_number',
        'payment_method',
        'status',
        'paid_at',
        'notes',
        'recorded_by',
        'rejection_reason',
        'rejected_at',
        'rejected_by',
        'rejection_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    /**
     * Get the user who rejected this deposit
     */
    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Check if rejection limit has been reached
     *
     * @return bool
     */
    public function hasReachedRejectionLimit()
    {
        return $this->rejection_count >= 3;
    }

    /**
     * Check if this deposit can be retried
     *
     * @return bool
     */
    public function canRetry()
    {
        return ($this->status === 'rejected' || $this->status === 'failed')
            && !$this->hasReachedRejectionLimit();
    }

    /**
     * Scope to get only rejected deposits
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->whereIn('status', ['rejected', 'failed']);
    }

    /**
     * Scope to get deposits by loan
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $loanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLoan($query, $loanId)
    {
        return $query->where('loan_id', $loanId);
    }
}
