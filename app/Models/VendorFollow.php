<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorFollow extends Model
{
    protected $fillable = [
        'vendor_id',
        'follower_id',
    ];
}
