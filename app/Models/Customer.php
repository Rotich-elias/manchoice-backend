<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
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
        'notes',
        // Motorcycle Details
        'motorcycle_number_plate',
        'motorcycle_chassis_number',
        'motorcycle_model',
        'motorcycle_type',
        'motorcycle_engine_cc',
        'motorcycle_colour',
        // Next of Kin Details
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relationship',
        'next_of_kin_email',
        'next_of_kin_passport_photo_path',
        // Guarantor Details
        'guarantor_name',
        'guarantor_phone',
        'guarantor_relationship',
        'guarantor_email',
        'guarantor_passport_photo_path',
        // Document Photos (stored on customer profile for reuse)
        'bike_photo_path',
        'logbook_photo_path',
        'passport_photo_path',
        'id_photo_front_path',
        'id_photo_back_path',
        'next_of_kin_id_front_path',
        'next_of_kin_id_back_path',
        'guarantor_id_front_path',
        'guarantor_id_back_path',
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
     * Get the user that owns the customer.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate outstanding balance for the customer.
     */
    public function outstandingBalance(): float
    {
        return $this->total_borrowed - $this->total_paid;
    }
}
