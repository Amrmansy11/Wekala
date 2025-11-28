<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Routing\Controller as BaseController;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class ConsumerController extends BaseController
{
    protected string $guard = 'consumer';

    public function __construct()
    {
        $this->middleware("auth:$this->guard-api");
    }


    protected function consumer(): ?Authenticatable
    {
        return Auth::guard("$this->guard-api")->user();
    }

    /**
     * Get the admin user's roles.
     */
    protected function roles()
    {
        return $this->consumer()?->getRoleNames();
    }

    /**
     * Get the admin user's permissions.
     */
    protected function permissions()
    {
        return $this->consumer()?->getAllPermissions();
    }
}
