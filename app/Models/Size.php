<?php

namespace App\Models;

use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Size extends Model
{
    protected $fillable = ['name', 'category_id', 'is_active'];

    protected $appends = ['products_count'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getProductsCountAttribute(): int
    {
        return ProductSize::where('size', $this->name)
            ->distinct('product_id')
            ->count('product_id');
    }
}
