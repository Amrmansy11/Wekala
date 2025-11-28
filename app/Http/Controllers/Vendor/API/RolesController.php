<?php

namespace App\Http\Controllers\Vendor\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\GeneralResource;
use App\Repositories\Vendor\RoleRepository;
use App\Http\Controllers\Vendor\API\VendorController;
use App\Http\Requests\Vendor\Api\Roles\StoreRoleRequest;
use App\Http\Requests\Vendor\Api\Roles\UpdateRoleRequest;

class RolesController extends VendorController
{
    public function __construct(protected RoleRepository $roleRepository)
    {
        // $this->middleware('permission:vendor_roles_view')->only('index');
        // $this->middleware('permission:vendor_roles_create')->only('create');
        // $this->middleware('permission:vendor_roles_update')->only('update');
        // $this->middleware('permission:vendor_roles_delete')->only('destroy');
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
            ->where('guard_name', 'vendor')
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
        $data['guard_name'] = 'vendor';
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
        $data['guard_name'] = 'vendor';
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
        $vendor = $this->roleRepository->delete($id);
        return response()->json(['data' => $vendor]);
    }
}
