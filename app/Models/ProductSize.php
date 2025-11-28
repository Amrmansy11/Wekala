<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property int $id
 * @property int $product_id
 * @property string $size
 * @property int $pieces_per_bag
 */
class ProductSize extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'size',
        'pieces_per_bag',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(ProductVariant::class, 'product_variant_size')
            ->withPivot('quantity', 'total_quantity')
            ->withTimestamps();
    }
}
