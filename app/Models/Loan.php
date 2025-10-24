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
        'deposit_amount',
        'deposit_paid',
        'deposit_required',
        'deposit_paid_at',
        'status',
        'disbursement_date',
        'due_date',
        'duration_days',
        'daily_payment_amount',
        'adjusted_duration_days',
        'purpose',
        'notes',
        'approved_by',
        'approved_at',
        // Photo paths for loan application
        'bike_photo_path',
        'logbook_photo_path',
        'passport_photo_path',
        'id_photo_front_path',
        'id_photo_back_path',
        'next_of_kin_id_front_path',
        'next_of_kin_id_back_path',
        'next_of_kin_passport_photo_path',
        'guarantor_id_front_path',
        'guarantor_id_back_path',
        'guarantor_passport_photo_path',
        'application_details',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'deposit_required' => 'boolean',
        'deposit_paid_at' => 'datetime',
        'daily_payment_amount' => 'decimal:2',
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
     * Get the payment schedule for this loan.
     */
    public function paymentSchedule(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
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

    /**
     * Check if loan should be marked as defaulted based on missed daily payments.
     * A loan is considered defaulted if:
     * - It has overdue payment schedule items
     * - Has missed 3 or more consecutive daily payments
     */
    public function shouldBeDefaulted(): bool
    {
        if (!in_array($this->status, ['approved', 'active'])) {
            return false;
        }

        // Check payment schedule for overdue items
        $overdueCount = $this->paymentSchedule()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->count();

        // Default if 3 or more payments are overdue
        return $overdueCount >= 3;
    }

    /**
     * Get count of missed payments
     */
    public function getMissedPaymentsCount(): int
    {
        return $this->paymentSchedule()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->count();
    }

    /**
     * Get total amount of overdue payments
     */
    public function getOverdueAmount(): float
    {
        $overdueSchedules = $this->paymentSchedule()
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        return $overdueSchedules->sum(function ($schedule) {
            return $schedule->expected_amount - $schedule->paid_amount;
        });
    }

    /**
     * Mark loan as defaulted
     */
    public function markAsDefaulted(?string $reason = null): void
    {
        $this->update([
            'status' => 'defaulted',
            'notes' => ($this->notes ?? '') . "\n\n[" . now()->toDateTimeString() . "] Marked as DEFAULTED" .
                       ($reason ? ": {$reason}" : " due to missed payments"),
        ]);
    }

    /**
     * Calculate daily payment amount with minimum of KES 200.
     * Returns array with daily_payment_amount, adjusted_duration_days, and due_date
     */
    public function calculateDailyPayment(): array
    {
        $totalAmount = $this->total_amount;
        $durationDays = $this->duration_days ?? 30; // Default 30 days
        $disbursementDate = $this->disbursement_date ?? now();

        $minimumDailyPayment = 200; // KES 200 minimum

        // Scenario 1: Total amount < KES 200 - Pay once
        if ($totalAmount < $minimumDailyPayment) {
            return [
                'daily_payment_amount' => $totalAmount,
                'adjusted_duration_days' => 1,
                'due_date' => $disbursementDate->copy()->addDay(),
            ];
        }

        // Calculate daily payment
        $calculatedDaily = $totalAmount / $durationDays;

        // Scenario 2: Calculated daily payment >= KES 200 - Use as is
        if ($calculatedDaily >= $minimumDailyPayment) {
            return [
                'daily_payment_amount' => round($calculatedDaily, 2),
                'adjusted_duration_days' => $durationDays,
                'due_date' => $disbursementDate->copy()->addDays($durationDays),
            ];
        }

        // Scenario 3: Calculated daily < KES 200 - Adjust duration
        $adjustedDuration = (int) ceil($totalAmount / $minimumDailyPayment);

        return [
            'daily_payment_amount' => $minimumDailyPayment,
            'adjusted_duration_days' => $adjustedDuration,
            'due_date' => $disbursementDate->copy()->addDays($adjustedDuration),
        ];
    }

    /**
     * Generate payment schedule for this loan
     */
    public function generatePaymentSchedule(): void
    {
        // Clear existing schedule if any
        $this->paymentSchedule()->delete();

        if (!$this->disbursement_date || !$this->adjusted_duration_days) {
            return;
        }

        $dailyAmount = $this->daily_payment_amount;
        $totalDays = $this->adjusted_duration_days;
        $disbursementDate = $this->disbursement_date;

        // Calculate last day remainder
        $totalExpected = $dailyAmount * ($totalDays - 1);
        $lastDayAmount = $this->total_amount - $totalExpected;

        for ($day = 1; $day <= $totalDays; $day++) {
            $dueDate = $disbursementDate->copy()->addDays($day);
            $expectedAmount = ($day == $totalDays) ? $lastDayAmount : $dailyAmount;

            PaymentSchedule::create([
                'loan_id' => $this->id,
                'day_number' => $day,
                'due_date' => $dueDate,
                'expected_amount' => $expectedAmount,
                'paid_amount' => 0,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Calculate required deposit amount (10% of total loan amount)
     */
    public function calculateDepositAmount(): float
    {
        return round($this->total_amount * 0.10, 2);
    }

    /**
     * Check if deposit is fully paid
     */
    public function isDepositPaid(): bool
    {
        if (!$this->deposit_required) {
            return true;
        }
        return $this->deposit_paid >= $this->deposit_amount;
    }

    /**
     * Get remaining deposit amount
     */
    public function getRemainingDepositAmount(): float
    {
        if (!$this->deposit_required) {
            return 0;
        }
        $remaining = $this->deposit_amount - $this->deposit_paid;
        return $remaining > 0 ? $remaining : 0;
    }

    /**
     * Get the deposits for this loan
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }
}
