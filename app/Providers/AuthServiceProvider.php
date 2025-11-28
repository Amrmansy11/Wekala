<?php

namespace App\Providers;

use App\Models\Vendor;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('access-switch-vendor', function ($user) {
            $request = app(Request::class);
            if (!$request->hasHeader('vendor-id')) {
                return false;
            }

            $vendorId = $request->header('vendor-id');
            return Vendor::query()->where(['uuid' => $vendorId, 'parent_id' => $user->vendor_id])->count() !== 0;
        });
    }
}
