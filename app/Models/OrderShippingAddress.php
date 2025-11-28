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
 * @property State $state
 * @property City $city
 * @property int $cart_id
 * @property int $state_id
 * @property int $city_id
 * @property string $address_type
 * @property string $recipient_name
 * @property string $recipient_phone
 * @property string $full_address
 */
class OrderShippingAddress extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'order_id',
        'state_id',
        'city_id',
        'address_type',
        'recipient_name',
        'recipient_phone',
        'full_address',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}


