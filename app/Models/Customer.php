<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'id_number',
        'address',
        'business_name',
        'status',
        'credit_limit',
        'total_borrowed',
        'total_paid',
        'loan_count',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'total_borrowed' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    /**
     * Get the loans for the customer.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get the payments for the customer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get active loans for the customer.
     */
    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)->whereIn('status', ['approved', 'active']);
    }

    /**
     * Calculate outstanding balance for the customer.
     */
    public function outstandingBalance(): float
    {
        return $this->total_borrowed - $this->total_paid;
    }
}
