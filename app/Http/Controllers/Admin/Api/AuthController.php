<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Api\Auth\LoginRequest;
use App\Http\Resources\AdminUserResource;
use App\Models\Admin;
use App\Repositories\Admin\AdminUserRepository;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    public function __construct(protected AdminUserRepository $adminUserRepository)
    {
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $this->adminUserRepository
            ->login($request->string('username'), $request->string('password'));
        if (!$admin) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }
        $tokenResult = $admin->createToken('admin-token');
        return response()->json([
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'data' => new AdminUserResource($admin),
        ]);
    }
}
