<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
        'description',
        'power',
        'warranty',
        'specifications',
        'images',
        'video_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'specifications' => 'array',
        'images' => 'array', // Now stores full URLs from cloud storage (e.g., Cloudinary, Imgbb)
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
