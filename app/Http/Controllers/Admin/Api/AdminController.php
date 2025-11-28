<?php

namespace App\Http\Controllers\Admin\Api;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class AdminController extends BaseController
{
    protected string $guard = 'admin';

    public function __construct()
    {
        $this->middleware("auth:$this->guard-api");
    }


    protected function admin(): ?Authenticatable
    {
        return Auth::guard("$this->guard-api")->user();
    }

    /**
     * Get the admin user's roles.
     */
    protected function roles()
    {
        return $this->admin()?->getRoleNames();
    }

    /**
     * Get the admin user's permissions.
     */
    protected function permissions()
    {
        return $this->admin()?->getAllPermissions();
    }
}
