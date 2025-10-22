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

    protected $appends = [
        'bike_photo_url',
        'logbook_photo_url',
        'passport_photo_url',
        'id_photo_front_url',
        'id_photo_back_url',
        'next_of_kin_id_front_url',
        'next_of_kin_id_back_url',
        'next_of_kin_passport_photo_url',
        'guarantor_id_front_url',
        'guarantor_id_back_url',
        'guarantor_passport_photo_url',
    ];

    /**
     * Get the full URL for bike photo
     */
    public function getBikePhotoUrlAttribute(): ?string
    {
        return $this->bike_photo_path ? asset('storage/' . $this->bike_photo_path) : null;
    }

    /**
     * Get the full URL for logbook photo
     */
    public function getLogbookPhotoUrlAttribute(): ?string
    {
        return $this->logbook_photo_path ? asset('storage/' . $this->logbook_photo_path) : null;
    }

    /**
     * Get the full URL for passport photo
     */
    public function getPassportPhotoUrlAttribute(): ?string
    {
        return $this->passport_photo_path ? asset('storage/' . $this->passport_photo_path) : null;
    }

    /**
     * Get the full URL for ID photo front
     */
    public function getIdPhotoFrontUrlAttribute(): ?string
    {
        return $this->id_photo_front_path ? asset('storage/' . $this->id_photo_front_path) : null;
    }

    /**
     * Get the full URL for ID photo back
     */
    public function getIdPhotoBackUrlAttribute(): ?string
    {
        return $this->id_photo_back_path ? asset('storage/' . $this->id_photo_back_path) : null;
    }

    /**
     * Get the full URL for next of kin ID front
     */
    public function getNextOfKinIdFrontUrlAttribute(): ?string
    {
        return $this->next_of_kin_id_front_path ? asset('storage/' . $this->next_of_kin_id_front_path) : null;
    }

    /**
     * Get the full URL for next of kin ID back
     */
    public function getNextOfKinIdBackUrlAttribute(): ?string
    {
        return $this->next_of_kin_id_back_path ? asset('storage/' . $this->next_of_kin_id_back_path) : null;
    }

    /**
     * Get the full URL for guarantor ID front
     */
    public function getGuarantorIdFrontUrlAttribute(): ?string
    {
        return $this->guarantor_id_front_path ? asset('storage/' . $this->guarantor_id_front_path) : null;
    }

    /**
     * Get the full URL for guarantor ID back
     */
    public function getGuarantorIdBackUrlAttribute(): ?string
    {
        return $this->guarantor_id_back_path ? asset('storage/' . $this->guarantor_id_back_path) : null;
    }

    /**
     * Get the full URL for next of kin passport photo
     */
    public function getNextOfKinPassportPhotoUrlAttribute(): ?string
    {
        return $this->next_of_kin_passport_photo_path ? asset('storage/' . $this->next_of_kin_passport_photo_path) : null;
    }

    /**
     * Get the full URL for guarantor passport photo
     */
    public function getGuarantorPassportPhotoUrlAttribute(): ?string
    {
        return $this->guarantor_passport_photo_path ? asset('storage/' . $this->guarantor_passport_photo_path) : null;
    }

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
