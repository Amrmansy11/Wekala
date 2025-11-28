<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Review extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'product_id',
        'rating',
        'comment',
        'has_images_or_videos',
        'is_repeat_customer',
        'reviewable_type',
        'reviewable_id'
    ];

    protected $casts = [
        'has_images_or_videos' => 'boolean',
        'is_repeat_customer' => 'boolean',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images_videos')->useDisk('public');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }
    public function isRepeatCustomer(): bool
    {
        return self::where('product_id', $this->product_id)
                ->where('reviewable_id', $this->reviewable_id)
                ->where('reviewable_type', $this->reviewable_type)
                ->count() > 1;
    }
}
