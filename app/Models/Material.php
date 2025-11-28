<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Material extends Model implements AuditableContract
{
    use HasTranslations, Auditable, SoftDeletes;

    public array $translatable = ['name'];
    protected $fillable = ['name', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
