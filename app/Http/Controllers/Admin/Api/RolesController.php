<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Requests\Admin\Api\Roles\StoreRoleRequest;
use App\Http\Requests\Admin\Api\Roles\UpdateRoleRequest;
use App\Http\Resources\GeneralResource;
use App\Repositories\Admin\RoleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolesController extends AdminController
{
    public function __construct(protected RoleRepository $roleRepository)
    {
        $this->middleware('permission:roles_view')->only('index');
        $this->middleware('permission:roles_create')->only('create');
        $this->middleware('permission:roles_update')->only('update');
        $this->middleware('permission:roles_delete')->only('destroy');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $roles = $this->roleRepository->query()
            ->where('guard_name', 'admin')
            ->paginate($perPage);
        return response()->json([
            'data' => GeneralResource::collection($roles),
            'pagination' => [
                'currentPage' => $roles->currentPage(),
                'total' => $roles->total(),
                'perPage' => $roles->perPage(),
                'lastPage' => $roles->lastPage(),
                'hasMorePages' => $roles->hasMorePages(),
            ]
        ]);
    }

    /**
     * @param StoreRoleRequest $request
     * @return JsonResponse
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $data = $request->all();
        $data['guard_name'] = 'admin';
        $role = $this->roleRepository->store($data);
        return response()->json(['data' => new GeneralResource($role)]);
    }

    /**
     * @param int $id
     * @param UpdateRoleRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateRoleRequest $request): JsonResponse
    {
        $data = $request->all();
        $data['guard_name'] = 'admin';
        $role = $this->roleRepository->update($data, $id);
        return response()->json(['data' => new GeneralResource($role)]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id): JsonResponse
    {
        $admin = $this->roleRepository->delete($id);
        return response()->json(['data' => $admin]);
    }
}
