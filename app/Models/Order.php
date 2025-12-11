<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * Represents a purchase order created from a cart checkout.
 *
 * @property int $id
 * @property int $buyer_vendor_id
 * @property int $seller_vendor_id
 * @property int $vendor_user_id
 * @property int $cart_id
 * @property float $subtotal
 * @property int $parent_id
 * @property float $discount
 * @property float $delivery
 * @property float $total
 * @property string $status
 * @property Collection $items
 * @property Vendor $vendor
 * @property VendorUser $vendorUser
 * @property Cart $cart
 * @property ?string $code
 */
class Order extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'buyer_vendor_id',
        'seller_vendor_id',
        'vendor_user_id',
        'parent_id',
        'cart_id',
        'subtotal',
        'discount',
        'delivery',
        'total',
        'status',
        'user_id',
        'code',
    ];

    public function buyerVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'buyer_vendor_id');
    }

    public function sellerVendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'seller_vendor_id');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(VendorUser::class, 'vendor_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
