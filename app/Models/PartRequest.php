<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'part_name',
        'description',
        'motorcycle_model',
        'year',
        'quantity',
        'budget',
        'urgency',
        'status',
        'admin_notes',
        'image_path',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Get the full URL for request image
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    /**
     * Get the customer that made this request
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user that made this request
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
