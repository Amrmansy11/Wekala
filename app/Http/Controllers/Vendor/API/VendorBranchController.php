<?php

namespace App\Http\Controllers\Vendor\API;

use App\Models\VendorUser;
use Illuminate\Http\JsonResponse;
use App\Models\CartShippingAddress;
use App\Http\Resources\VendorUserResource;
use App\Http\Resources\BranchSwitchResource;
use App\Http\Resources\BranchDetailsResource;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Requests\Vendor\Api\VendorBranch\StoreVendorBranchUserRequest;


class VendorBranchController extends VendorController
{
    public function __construct(protected VendorUserRepository $vendorUserRepository, protected VendorRepository $vendorRepository)
    {
        // $this->middleware('permission:vendor_branch_user_create')->only('storeUser');
        // $this->middleware('permission:vendor_branch_create')->only('firstStep', 'secondStep');
        parent::__construct();
    }



    public function switchBranch(): JsonResponse
    {
        /** @var VendorUser $loggedInVendor */
        $loggedInVendor = auth()->user();
        $branchs = $this->vendorRepository->query()->where('parent_id', $loggedInVendor->vendor_id)->get();
        return response()->json([
            'data' => BranchSwitchResource::collection($branchs)
        ]);
    }

    public function branchDetails(): JsonResponse
    {
        /** @var VendorUser $loggedInVendor */
        $loggedInVendor = auth()->user();
        $branchs = $this->vendorRepository->query()->where('parent_id', $loggedInVendor->vendor_id)->get();
        return response()->json([
            'data' => BranchDetailsResource::collection($branchs)
        ]);
    }


    /**
     * @param StoreVendorBranchUserRequest $request
     * @return JsonResponse
     */
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

        /** @var VendorUser $loggedInVendor */
        $loggedInVendor = auth()->user();

        $vendor = $this->vendorRepository->store([
            'parent_id' => $loggedInVendor->vendor_id,
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

        CartShippingAddress::query()->create([
            'addressable_type' => VendorUser::class,
            'addressable_id' => $vendorUser->id,
            'address_type' => $request->input('address_type') ?? 'Home',
            'recipient_name' => $vendorUser->name,
            'recipient_phone' => $vendorUser->phone,
            'full_address' => $vendor->address,
            'state_id' => $vendor->state_id,
            'city_id' => $vendor->city_id,
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
            'data' => new VendorUserResource($vendorUser)
        ]);
    }
}
