<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Requests\Admin\Api\AdminUsers\StoreAdminUserRequest;
use App\Http\Requests\Admin\Api\AdminUsers\UpdateAdminUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Repositories\Admin\AdminUserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends AdminController
{
    /**
     * @param AdminUserRepository $adminUserRepository
     */
    public function __construct(protected AdminUserRepository $adminUserRepository)
    {
        $this->middleware('permission:admin_users_view')->only('index');
        $this->middleware('permission:admin_users_create')->only('store');
        $this->middleware('permission:admin_users_update')->only('update');
        $this->middleware('permission:admin_users_delete')->only('destroy');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $users = $this->adminUserRepository->paginate($perPage);
        return response()->json([
            'data' => AdminUserResource::collection($users),
            'pagination' => [
                'currentPage' => $users->currentPage(),
                'total' => $users->total(),
                'perPage' => $users->perPage(),
                'lastPage' => $users->lastPage(),
                'hasMorePages' => $users->hasMorePages(),
            ]
        ]);
    }

    /**
     * @param StoreAdminUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $admin = $this->adminUserRepository->store($request->all());
        return response()->json(['data' => new AdminUserResource($admin)]);
    }

    /**
     * @param int $id
     * @param UpdateAdminUserRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateAdminUserRequest $request): JsonResponse
    {
        $admin = $this->adminUserRepository->update($request->all(), $id);
        return response()->json(['data' => new AdminUserResource($admin)]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id): JsonResponse
    {
        $admin = $this->adminUserRepository->delete($id);
        return response()->json(['data' => $admin]);
    }
}
