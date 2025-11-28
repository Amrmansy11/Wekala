<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Color extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'hex_code', 'color', 'is_active'];



    public function products(): HasManyThrough
    {
        return $this->hasManyThrough(
            Product::class,
            ProductVariant::class,
            'color',
            'id',
            'color',
            'product_id'
        );
    }
}
