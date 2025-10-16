<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'loan_number',
        'principal_amount',
        'interest_rate',
        'total_amount',
        'amount_paid',
        'balance',
        'status',
        'disbursement_date',
        'due_date',
        'duration_days',
        'purpose',
        'notes',
        'approved_by',
        'approved_at',
        // Photo paths for loan application
        'bike_photo_path',
        'logbook_photo_path',
        'passport_photo_path',
        'id_photo_path',
        'next_of_kin_id_photo_path',
        'next_of_kin_passport_photo_path',
        'guarantor_id_photo_path',
        'guarantor_passport_photo_path',
        'application_details',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'disbursement_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the loan.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who approved the loan.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payments for the loan.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the loan items (products) for this loan.
     */
    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    /**
     * Get the total value of products in this loan
     */
    public function getTotalProductsValueAttribute(): float
    {
        return $this->items()->sum('subtotal');
    }

    /**
     * Check if loan is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date || $this->status === 'completed') {
            return false;
        }

        return $this->due_date->isPast() && $this->balance > 0;
    }

    /**
     * Calculate days overdue.
     */
    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }
}
