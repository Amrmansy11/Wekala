<?php

namespace App\Http\Controllers\Admin\Api;

use Illuminate\Http\JsonResponse;

class DashboardController extends AdminController
{
    public function __construct()
    {
        $this->middleware('permission:dashboard_view')->only('index');
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'admin' => $this->admin(),
            'roles' => $this->roles(),
            'permissions' => $this->permissions(),
        ]);
    }
}
