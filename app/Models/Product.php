<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'price',
        'original_price',
        'discount_percentage',
        'image_path',
        'stock_quantity',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_percentage' => 'integer',
        'is_available' => 'boolean',
    ];

    protected $appends = ['image_url'];

    /**
     * Get the full URL for the product image
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            // Check if it's already a full URL (external image)
            if (filter_var($this->image_path, FILTER_VALIDATE_URL)) {
                return $this->image_path;
            }
            // Otherwise, it's a local file path
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0 && $this->is_available;
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): void
    {
        $this->stock_quantity -= $quantity;
        if ($this->stock_quantity <= 0) {
            $this->is_available = false;
        }
        $this->save();
    }

    /**
     * Increase stock quantity
     */
    public function addStock(int $quantity): void
    {
        $this->stock_quantity += $quantity;
        if ($this->stock_quantity > 0 && !$this->is_available) {
            $this->is_available = true;
        }
        $this->save();
    }
}
