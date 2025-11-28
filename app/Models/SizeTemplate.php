<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $template_name
 * @property int $vendor_id
 * @property numeric $chest
 * @property numeric $chest_pattern
 * @property numeric $product_length
 * @property numeric $length_pattern
 * @property numeric $weight_from
 * @property numeric $weight_from_pattern
 * @property numeric $weight_to
 * @property numeric $weight_to_pattern
 * @property Vendor $vendor
 */
class SizeTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'template_name',
        'chest',
        'chest_pattern',
        'product_length',
        'length_pattern',
        'weight_from',
        'weight_from_pattern',
        'weight_to',
        'weight_to_pattern',
    ];

    public function vendor() : BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
