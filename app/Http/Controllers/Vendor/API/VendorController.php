<?php

namespace App\Http\Controllers\Vendor\API;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class VendorController extends BaseController
{
    protected string $guard = 'vendor';

    public function __construct()
    {
        $this->middleware("auth:$this->guard-api");
    }


    protected function vendor(): ?Authenticatable
    {
        return Auth::guard("$this->guard-api")->user();
    }

    /**
     * Get the admin user's roles.
     */
    protected function roles()
    {
        return $this->vendor()?->getRoleNames();
    }

    /**
     * Get the admin user's permissions.
     */
    protected function permissions()
    {
        return $this->vendor()?->getAllPermissions();
    }
}
