<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ElwekalaCollection extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['type', 'product_id', 'type_elwekala'];
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
