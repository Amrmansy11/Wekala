<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Discount model representing discounts offered by vendors.
 *
 * @property int $id
 * @property string $title
 * @property float $percentage
 * @property int $vendor_id
 * @property Carbon|null $archived_at
 */
class Discount extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'percentage',
        'vendor_id',
        'archived_at',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'archived_at' => 'datetime',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'discount_products');
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




