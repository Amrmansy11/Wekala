<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $desc
 * @property string $type
 */
class Policy extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name', 'title', 'desc'];
    protected $fillable = ['name', 'title', 'desc', 'type'];
}
