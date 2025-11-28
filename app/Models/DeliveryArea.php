<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $vendor_id
 * @property int $state_id
 * @property int $city_id
 * @property string $district
 * @property float $price
 */
class DeliveryArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'state_id',
        'city_id',
        'district',
        'price',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
    public function scopeSearch($query, $search): Builder
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('price', 'like', "%{$search}%")
                ->orWhere('district', 'like', "%{$search}%")
                ->orWhereHas('state', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('city', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                });
        });
    }
}
