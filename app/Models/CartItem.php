<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property Cart $cart
 * @property Vendor $vendor
 * @property Product $product
 * @property int $cart_id
 * @property int $vendor_id
 * @property int $user_id
 * @property int $vendor_user_id
 * @property int $product_id
 * @property int $product_variant_id
 * @property int $quantity
 * @property float $unit_price
 */
class CartItem extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'cart_id',
        'vendor_id',
        'vendor_user_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'product_size_id',
        'color'
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(VendorUser::class, 'vendor_user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}


