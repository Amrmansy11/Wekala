<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasTranslations, SoftDeletes;

    public array $translatable = ['name'];
    protected $fillable = ['name'];
}
