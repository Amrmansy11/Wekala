<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedProduct extends Model
{
    protected $fillable = [
        'id',
        'feed_id',
        'product_id',
    ];
}
