<?php

namespace App\Http\Controllers\Vendor\Api;

use App\Helpers\AppHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\VendorUserResource;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Controllers\Vendor\API\VendorController;
use App\Http\Requests\Vendor\Api\VendorUser\StoreVendorUserRequest;
use App\Http\Requests\Vendor\Api\VendorUser\UpdateVendorUserRequest;

class VendorUserController extends VendorController
{
    public function __construct(protected VendorUserRepository $vendorUserRepository)
    {
        // $this->middleware('permission:vendor_vendor_users_view')->only('index');
        // $this->middleware('permission:vendor_vendor_users_create')->only('create');
        // $this->middleware('permission:vendor_vendor_users_update')->only('update');
        // $this->middleware('permission:vendor_vendor_users_delete')->only('destroy');
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $roles = $this->vendorUserRepository->query()
            ->where('vendor_id', auth()->user()->vendor_id)
            ->whereNot('id', auth()->user()->id)
            ->paginate($perPage);
        return response()->json([
            'data' => VendorUserResource::collection($roles),
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
     * @param StoreVendorUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreVendorUserRequest $request): JsonResponse
    {
        $data = $request->all();
        $data['vendor_id'] = AppHelper::getVendorId();
        $role = $this->vendorUserRepository->store($data);
        return response()->json(['data' => new VendorUserResource($role)]);
    }

    /**
     * @param int $id
     * @param UpdateVendorUserRequest $request
     * @return JsonResponse
     */
    public function update(int $id, UpdateVendorUserRequest $request): JsonResponse
    {
        $data = $request->all();
        $data['vendor_id'] = AppHelper::getVendorId();
        $role = $this->vendorUserRepository->update($data, $id);
        return response()->json(['data' => new VendorUserResource($role)]);
    }

    /**
     * @return JsonResponse
     */
    public function show(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['data' => new VendorUserResource($user)]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(int $id): JsonResponse
    {
        $vendor = $this->vendorUserRepository->delete($id);
        return response()->json(['data' => $vendor]);
    }
}
