<?php

namespace App\Http\Controllers\Vendor\API;

use Exception;
use App\Models\Vendor;
use App\Helpers\AppHelper;
use App\Models\VendorUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BrandResource;
use App\Repositories\Vendor\BrandRepository;
use App\Http\Requests\Vendor\Api\Brands\BrandStoreRequest;
use App\Http\Requests\Vendor\Api\Brands\BrandUpdateRequest;


class BrandController extends VendorController
{
    public function __construct(protected BrandRepository $brandRepository)
    {
        // $this->middleware('permission:vendor_brands_view')->only('index');
        // $this->middleware('permission:vendor_brands_create')->only('store');
        // $this->middleware('permission:vendor_brands_update')->only('update');
        // $this->middleware('permission:vendor_brands_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $brands = $this->brandRepository->query()
            ->where('vendor_id', AppHelper::getVendorId())
            ->orWhere('vendor_id', null)
            ->with('category')
            ->paginate($perPage);
        return response()->json([
            'data' => BrandResource::collection($brands),
            'pagination' => [
                'currentPage' => $brands->currentPage(),
                'total' => $brands->total(),
                'perPage' => $brands->perPage(),
                'lastPage' => $brands->lastPage(),
                'hasMorePages' => $brands->hasMorePages(),
            ]
        ]);
    }


    public function store(BrandStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $data['creatable_id'] = auth()->user()->id;
        $data['creatable_type'] = Vendor::class;
        $brand = $this->brandRepository->store($data);
        if ($request->hasFile('logo')) {
            $brand->addMedia($request->file('logo'))
                ->usingName($brand->getTranslation('name', 'en'))
                ->toMediaCollection('brand_logo');
        }
        return response()->json(['data' => new BrandResource($brand)]);
    }


    public function update(BrandUpdateRequest $request, $brand): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $exists = $this->brandRepository
            ->query()
            ->where('id', $brand)
            ->where('vendor_id', $data['vendor_id'])
            ->first();

        if (!$exists) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        $data['creatable_id'] = auth()->user()->id;
        $data['creatable_type'] = VendorUser::class;
        $brand = $this->brandRepository->update($data, $brand);

        if ($request->hasFile('logo')) {
            $brand->clearMediaCollection('brand_logo');
            $brand->addMedia($request->file('logo'))
                ->usingName($brand->getTranslation('name', 'en'))
                ->toMediaCollection('brand_logo');
        }

        return response()->json(['data' => new BrandResource($brand)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($brand): JsonResponse
    {
        $brand = $this->brandRepository->delete($brand);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return response()->json(['data' => true]);
    }

    public function toggleIsActive($brand): JsonResponse
    {
        $brand = $this->brandRepository->toggleIsActive($brand);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return response()->json(['data' => new BrandResource($brand)]);
    }
}
