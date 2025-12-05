<?php

namespace App\Models;

use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $barcode
 * @property string $type
 * @property float $wholesale_price
 * @property float $consumer_price
 * @property int $category_id
 * @property int $sub_category_id
 * @property int $sub_sub_category_id
 * @property int $brand_id
 * @property int $size_chart
 * @property int $stock
 * @property int $stock_b2b
 * @property int $stock_b2c
 * @property int $min_color
 * @property int $vendor_id
 * @property int $parent_id
 * @property Carbon $published_at
 * @property int $elwekala_policy
 * @property Category $category
 * @property Vendor $vendor
 *
 */
class Product extends Model implements HasMedia, AuditableContract
{
    use HasFactory, Auditable, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'material_id',
        'barcode',
        'wholesale_price',
        'consumer_price',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'brand_id',
        'stock',
        'stock_b2b',
        'stock_b2c',
        'min_color',
        'published_at',
        'elwekala_policy',
        'status',
        'vendor_id',
        'parent_id',
        'type',
    ];

    protected $casts = [
        'images' => 'array',
        'elwekala_policy' => 'bool',
        'published_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->useDisk('public');
        $this->addMediaCollection('size_chart')->useDisk('public');
        $this->addMediaCollection('variant_images')->useDisk('public');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function subSubCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_sub_category_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productMeasurement(): HasMany
    {
        return $this->hasMany(ProductMeasurement::class);
    }

    public function creatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isClothing(): bool
    {
        return $this->category && $this->category->size_required == true;
    }

    public function scopeFilter($query, $filters): Builder
    {
        return $query
            ->when(
                $filters['search'],
                fn($q, $search) => $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
            )
            ->when($filters['category_id'], fn($q) => $q->whereIn('category_id', (array)$filters['category_id']))
            ->when($filters['tag_id'], fn($q) => $q->whereHas('tags', fn($sq) => $sq->whereIn('tag_id', (array)$filters['tag_id'])))
            ->when($filters['size_id'], fn($q) => $q->whereHas('sizes', fn($sq) => $sq->whereIn('id', (array)$filters['size_id'])))
            ->when($filters['color_id'], fn($q) => $q->whereHas('variants', fn($sq) => $sq->whereIn('id', (array)$filters['color_id'])))
            ->when($filters['material_id'], fn($q) => $q->whereIn('material_id', (array)$filters['material_id']))
            ->when($filters['brand_id'] ?? null, fn($q) => $q->whereIn('brand_id', (array)$filters['brand_id']));
    }

    public function collections()
    {
        return $this->hasMany(ElwekalaCollection::class);
    }

    public function isBestSeller(): bool
    {
        return $this->collections()->where('type', 'best_sellers')->exists();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sliders()
    {
        return $this->belongsToMany(Slider::class, 'slider_product', 'product_id', 'slider_id');
    }

    public function feeds(): BelongsToMany
    {
        return $this->belongsToMany(Feed::class, 'feed_products');
    }

    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'voucher_product');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'line_total']);
    }

    public function points(): BelongsToMany
    {
        return $this->belongsToMany(Point::class);
    }

    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_products');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function scopeB2BB2C(Builder $query): Builder
    {
        return $query->whereIn('type', ['b2b_b2c', 'b2c']);
    }
}
