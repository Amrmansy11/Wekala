<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property int $id
 * @property int $parent_id
 * @property boolean $is_active
 * @property string $image
 * @property boolean $size_required
 * @property string $size
 */
class Category extends Model implements HasMedia, AuditableContract
{
    use HasTranslations, SoftDeletes, Auditable, InteractsWithMedia;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'parent_id', 'is_active', 'size_required', 'size'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function packingUnits(): HasMany
    {
        return $this->hasMany(PackingUnit::class);
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(Size::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function vendors(): HasMany
    {
        return $this->hasMany(Vendor::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('category_image')->useDisk('public');
    }
}
