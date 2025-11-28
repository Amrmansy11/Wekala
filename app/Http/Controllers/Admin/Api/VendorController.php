<?php

namespace App\Http\Controllers\Admin\Api;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\VendorAdminResource;
use App\Repositories\Admin\VendorRepository;
use App\Http\Resources\AdminVendorUserResource;
use App\Http\Resources\VendorShowAdminResource;
use App\Http\Resources\OwnerVendorAdminResource;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Requests\Admin\Api\Vendors\ChengeStatusVendor;
use App\Http\Requests\Admin\Api\Vendors\VendorStoreRequest;
use App\Http\Requests\Admin\Api\Vendors\DoumentsUpdateRequest;
use App\Http\Requests\Admin\Api\Vendors\OwnerInfoUpdateRequest;
use App\Http\Requests\Admin\Api\Vendors\VendorStoreInfoUpdateRequest;

class VendorController extends AdminController
{
    public function __construct(protected VendorUserRepository $vendorUserRepository, protected VendorRepository $vendorRepository)
    {
        $this->middleware('permission:vendors_view')->only('index');
        $this->middleware('permission:vendors_create')->only('store');
        $this->middleware('permission:vendors_update')->only('update');
        $this->middleware('permission:vendors_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $storeType = $request->string('store_type', 'seller');
        $status = $request->string('status', 'pending');

        $vendors = $this->vendorRepository->query()
            ->when($storeType, fn($q) => $q->where('store_type', $storeType))
            ->when($status, fn($q) => $q->where('status', $status))
            ->where('parent_id', null)
            ->withCount('followers', 'following', 'branches', 'products')
            ->paginate($perPage);
        return response()->json([
            'data' => VendorAdminResource::collection($vendors),
            'pagination' => [
                'currentPage' => $vendors->currentPage(),
                'total' => $vendors->total(),
                'perPage' => $vendors->perPage(),
                'lastPage' => $vendors->lastPage(),
                'hasMorePages' => $vendors->hasMorePages(),
            ]
        ]);
    }


    public function store(VendorStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var VendorUser $vendorUser */
        $vendorUser = $this->vendorUserRepository->store([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'main_account' => 1,
            'is_active' => 1,
        ]);
        if ($request->hasFile('image')) {
            $vendorUser->addMedia($request->file('image'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_user');
        }

        $vendor = $this->vendorRepository->store([
            'store_type' => $data['store_type'],
            'store_name' => ['en' => $data['store_name'], 'ar' => $data['store_name']],
            'phone' => $data['phone_vendor'],
            'category_id' => $data['category_id'],
            'state_id' => $data['state_id'],
            'city_id' => $data['city_id'],
            'address' => [
                'en' => $data['address'] ?? null,
                'ar' => $data['address'] ?? null,
            ],
            'description' => [
                'en' => $data['description'] ?? null,
                'ar' => $data['description'] ?? null,
            ],
            'status' => 'pending'
        ]);


        $vendorUser->update(['vendor_id' => $vendor->id]);

        if ($request->hasFile('logo')) {
            $vendor->addMedia($request->file('logo'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_logo');
        }
        if ($request->hasFile('cover')) {
            $vendor->clearMediaCollection('vendor_cover');
            $vendor->addMedia($request->file('cover'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_cover');
        }
        if ($request->hasFile('national_id_file')) {
            $vendor->addMedia($request->file('national_id_file'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_national_id');
        }
        if ($request->hasFile('tax_card_file')) {
            $vendor->addMedia($request->file('tax_card_file'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_tax_card');
        }

        return response()->json([
            'data' => new VendorAdminResource($vendor),
            'message' => 'Vendor User Added Successfully.',
        ]);
    }
    public function storeInfoUpdate(VendorStoreInfoUpdateRequest $request, $vendor): JsonResponse
    {
        $vendor = $this->vendorRepository->update($request->all(), $vendor);
        if ($request->hasFile('logo')) {
            $vendor->clearMediaCollection('vendor_logo');
            $vendor->addMedia($request->file('logo'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_logo');
        }
        if ($request->hasFile('cover')) {
            $vendor->clearMediaCollection('vendor_cover');
            $vendor->addMedia($request->file('cover'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_cover');
        }


        return response()->json([
            'data' => new VendorAdminResource($vendor),
            'message' => 'Vendor Updated Successfully.',
        ]);
    }
    public function doumentsUpdate(DoumentsUpdateRequest $request, $vendor): JsonResponse
    {
        $vendor = $this->vendorRepository->find($vendor);
        if ($request->hasFile('national_id_file')) {
            $vendor->clearMediaCollection('vendor_national_id');
            $vendor->addMedia($request->file('national_id_file'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_national_id');
        }
        if ($request->hasFile('tax_card_file')) {
            $vendor->clearMediaCollection('vendor_tax_card');
            $vendor->addMedia($request->file('tax_card_file'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_tax_card');
        }
        return response()->json([
            'data' => new VendorAdminResource($vendor),
            'message' => 'Vendor Updated Successfully.'
        ]);
    }
    public function ownerInfoUpdate(OwnerInfoUpdateRequest $request, $vendorUser): JsonResponse
    {
        $vendorUser = $this->vendorUserRepository->update($request->all(), $vendorUser);
        if ($request->hasFile('image')) {
            $vendorUser->clearMediaCollection('vendor_user');
            $vendorUser->addMedia($request->file('image'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_user');
        }
        return response()->json([
            'data' => new AdminVendorUserResource($vendorUser),
            'message' => 'Vendor User Updated Successfully.'
        ]);
    }
    public function show($vendor): JsonResponse
    {
        $vendor = $this->vendorRepository->with(['users'])->where('parent_id', null)->withCount('followers', 'following', 'branches', 'products')->find($vendor);
        return response()->json(['data' => new VendorShowAdminResource($vendor),]);
    }

    public function changeStatus(ChengeStatusVendor $request, $vendor): JsonResponse
    {
        $vendor = $this->vendorRepository->update(['status' => $request->status], $vendor);
        return response()->json(['data' => new VendorAdminResource($vendor),]);
    }
}
