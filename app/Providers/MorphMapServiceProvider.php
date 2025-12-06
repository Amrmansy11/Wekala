<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorUser;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Relation::enforceMorphMap([
            'vendor' => Vendor::class,
            'vendorUser' => VendorUser::class,
            'user' => User::class,
        ]);
    }
}
