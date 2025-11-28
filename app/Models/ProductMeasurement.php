<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class ProductMeasurement extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $fillable = [
        'product_id',
        'size',
        'waist',
        'length',
        'chest',
        'weight_range',
        'bundles',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
