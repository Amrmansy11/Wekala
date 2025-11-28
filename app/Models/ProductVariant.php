<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

/**
 * @property int $bags
 * @property string $color
 * @property int $total_pieces
 * @property int|null $total_pieces_b2c
 * @property int|null $total_pieces_b2b
 * @property int|null $quantity_b2b
 * @property int|null $quantity_b2c
 * @property Collection $sizes
 */
class ProductVariant extends Model implements HasMedia, AuditableContract
{
    use HasFactory, Auditable, InteractsWithMedia;

    protected $fillable = [
        'color',
        'bags',
        'total_pieces',
        'quantity_b2b',
        'quantity_b2c',
        'total_pieces_b2c',
        'total_pieces_b2b',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function colorHex(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'color', 'color');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(ProductSize::class, 'product_variant_size')
            ->withPivot('quantity', 'total_quantity')
            ->withTimestamps();
    }
}
