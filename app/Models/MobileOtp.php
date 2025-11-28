<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileOtp extends Model
{
    use SoftDeletes;

    protected $fillable = ['otp_type', 'otp_value', 'verification_code', 'vendor_user_id', 'action', 'expires_at'];

    public function vendorUser(): BelongsTo
    {
        return $this->belongsTo(VendorUser::class);
    }
}
