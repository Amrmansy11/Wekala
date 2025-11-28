<?php

namespace App\Models;

use App\Models\Product;
use App\Enums\PointType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Point extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'points',
        'vendor_id',
        'archived_at',
    ];

    protected $casts = [
        'type' => PointType::class,
        'archived_at' => 'datetime',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'point_product');
    }

    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isArchived(): bool
    {
        return !is_null($this->archived_at);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }
}


