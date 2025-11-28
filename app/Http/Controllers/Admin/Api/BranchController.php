<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\VendorAdminResource;
use App\Http\Resources\Admin\branchResource;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Http\Requests\Admin\Api\VendorBranch\StoreVendorBranchUserRequest;

class BranchController extends AdminController
{

    public function __construct(protected VendorUserRepository $vendorUserRepository, protected VendorRepository $vendorRepository)
    {
        //         $this->middleware('permission:branch_view')->only('index');
        //        $this->middleware('permission:branch_create')->only('store');
        //        $this->middleware('permission:branch_update')->only('update');
        //        $this->middleware('permission:branch_delete')->only('destroy');
        //
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search', null);
        $vendorId = $request->integer('vendor_id', null);

        $vendors = $this->vendorRepository->query()
            ->whereNotNull('parent_id')
            ->when($vendorId, fn($query) => $query->where('parent_id', $vendorId))
            ->when($search, fn($query) => $query->where('store_name', 'like', "%{$search}%"))
            ->paginate($perPage);


        return response()->json([
            'data' => branchResource::collection($vendors),
            'pagination' => [
                'currentPage' => $vendors->currentPage(),
                'total' => $vendors->total(),
                'perPage' => $vendors->perPage(),
                'lastPage' => $vendors->lastPage(),
                'hasMorePages' => $vendors->hasMorePages(),
            ]
        ]);
    }

    public function store(StoreVendorBranchUserRequest $request): JsonResponse
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
            $vendorUser->clearMediaCollection('vendor_user');
            $vendorUser->addMedia($request->file('image'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_user');
        }

        /** @var VendorUser $loggedInVendor */
        $loggedInVendor = $request->vendor_user_id;

        $vendor = $this->vendorRepository->store([
            'parent_id' => $loggedInVendor,
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
        if ($request->hasFile('logo')) {
            $vendor->addMedia($request->file('logo'))
                ->usingName($vendorUser->name)
                ->toMediaCollection('vendor_logo');
        }
        $vendorUser->update(['vendor_id' => $vendor->id]);

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
            'message' => 'Vendor User Added Successfully.',
            'data' => new VendorAdminResource($vendorUser)
        ]);
    }
}
