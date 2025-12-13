<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model implements HasMedia
{
    use InteractsWithMedia, HasFactory;

    protected $fillable = [
        'name',
        'desc',
        'start',
        'end',
        'type',
        'discount',
        'buy',
        'get',
        'quantity',
        'amount',
        'vendor_id'
    ];

    protected array $dates = ['start', 'end', 'created_at', 'updated_at'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'offer_products');
    }

    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeActive($query)
    {
        return $query->where('start', '<=', now())
            ->where('end', '>=', now());
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!$status) {
            return $query;
        }

        return match ($status) {
            'active' => $query->where('start', '<=', now())
                ->where('end', '>=', now()),

            'expired' => $query->where('end', '<', now()),

            default => $query,
        };
    }
}
