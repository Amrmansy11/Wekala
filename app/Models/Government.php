<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property json $name
 * @property int $city_id
 */
class Government extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'city_id'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
