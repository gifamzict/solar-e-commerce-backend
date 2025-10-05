<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'specifications' => 'array',
        'images' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
