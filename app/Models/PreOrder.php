<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreOrder extends Model
{
    protected $fillable = [
        'product_name',
        'category_id',
        'pre_order_price',
        'deposit_percentage',
        'expected_availability',
        'power_output',
        'warranty_period',
        'specifications',
        'images',
        'video_url',
    ];

    protected $casts = [
        'pre_order_price' => 'decimal:2',
        'deposit_percentage' => 'decimal:2',
        'images' => 'array', // Now stores full URLs from cloud storage (e.g., Cloudinary, Imgbb)
    ];

    protected $appends = ['deposit_amount'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relationship: Pre-order has many customer pre-orders
     */
    public function customerPreOrders(): HasMany
    {
        return $this->hasMany(CustomerPreOrder::class);
    }

    /**
     * Calculate the deposit amount based on percentage
     */
    public function getDepositAmountAttribute(): float
    {
        return ($this->pre_order_price * $this->deposit_percentage) / 100;
    }
}
