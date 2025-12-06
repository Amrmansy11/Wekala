<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property int $id
 * @property string $store_type
 * @property string $store_name
 * @property string $phone
 * @property int $parent_id
 * @property int $category_id
 * @property int $state_id
 * @property int $city_id
 * @property string $description
 * @property string $address
 * @property string $logo
 */
class Vendor extends Model implements HasMedia, AuditableContract
{
    use HasTranslations, SoftDeletes, Auditable, InteractsWithMedia;


    public array $translatable = ['store_name', 'address', 'description'];
    protected $fillable = [
        'store_type',
        'store_name',
        'phone',
        'category_id',
        'state_id',
        'city_id',
        'address',
        'description',
        'status',
        'parent_id',
    ];


    protected static function boot()
    {
        parent::boot();
        static::creating(function ($vendor) {
            if (empty($vendor->uuid)) {
                $vendor->uuid = (string)Str::uuid();
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(VendorUser::class);
    }


    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vendor_logo')->useDisk('public');
        $this->addMediaCollection('vendor_cover')->useDisk('public');
        $this->addMediaCollection('vendor_national_id')->useDisk('public');
        $this->addMediaCollection('vendor_tax_card')->useDisk('public');
    }

    public function vendorUsers(): HasOne
    {
        return $this->hasOne(VendorUser::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            Vendor::class,
            'vendor_follows',
            'vendor_id',
            'follower_id'
        );
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            Vendor::class,
            'vendor_follows',
            'follower_id',
            'vendor_id'
        );
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function branches()
    {
        return $this->hasMany(Vendor::class, 'parent_id');
    }

    public function feeds(): HasMany
    {
        return $this->hasMany(Feed::class);
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class, 'vendor_id');
    }
    public function gifts(): HasMany
    {
        return $this->hasMany(Gift::class, 'vendor_id');
    }

    public function vouchers(): MorphMany
    {
        return $this->morphMany(Voucher::class, 'creatable');
    }

    public function offers(): MorphMany
    {
        return $this->morphMany(Offer::class, 'creatable');
    }

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'vendor_id');
    }
}
