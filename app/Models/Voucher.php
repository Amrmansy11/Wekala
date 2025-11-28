<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'code',
        'percentage',
        'amount',
        'number_of_use',
        'number_of_use_per_person',
        'for_all',
        'start_date',
        'end_date',
        'vendor_id'
    ];

    protected array $dates = ['start_date', 'end_date'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'voucher_product');
    }

    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }
    public function scopeStatus($query, ?string $status)
    {
        if (!$status) {
            return $query;
        }

        return match ($status) {
            'active' => $query->where('start_date', '<=', now())
                ->where('end_date', '>=', now()),

            'expired' => $query->where('end_date', '<', now()),

            default => $query,
        };
    }
}
