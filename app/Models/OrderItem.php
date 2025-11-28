<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * Line item for an order.
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $product_variant_id
 * @property int|null $discount_id
 * @property int $quantity
 * @property float $unit_price
 * @property float $discount_percentage
 * @property float $discount_amount
 * @property float $price_after_discount
 * @property float $line_total
 * @property float $line_total_after_discount
 */
class OrderItem extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'order_id',
        'vendor_id',
        'vendor_user_id',
        'product_id',
        'product_variant_id',
        'discount_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'price_after_discount',
        'line_total',
        'line_total_after_discount',
        'product_size_id',
        'color'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}


