<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\HasMedia;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property int $vendor_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property boolean $is_active
 * @property Vendor $vendor
 * @property-read Collection<CartShippingAddress> $addresses
 */
class VendorUser extends Authenticatable implements HasMedia
{
    use Notifiable;
    use HasRoles;
    use HasApiTokens;
    use InteractsWithMedia;


    protected string $guard_name = 'vendor';
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'vendor_id',
        'is_active',
        'main_account',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * @return BelongsTo
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('vendor_user')->useDisk('public');
    }
    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'vendor_id', 'vendor_id');
    }
    public function wishlist() : MorphMany
    {
        return $this->morphMany(Wishlist::class, 'userable');
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(CartShippingAddress::class, 'addressable');
    }
}
