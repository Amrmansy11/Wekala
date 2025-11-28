<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use App\Models\Admin;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BrandResource;
use App\Http\Resources\BrandShowResource;
use App\Repositories\Admin\BrandRepository;
use App\Http\Requests\Admin\Api\Brands\BrandStoreRequest;
use App\Http\Requests\Admin\Api\Brands\BrandUpdateRequest;

class BrandController extends AdminController
{
    public function __construct(protected BrandRepository $brandRepository)
    {
        $this->middleware('permission:brands_view')->only('index');
        $this->middleware('permission:brands_create')->only('store');
        $this->middleware('permission:brands_update')->only('update');
        $this->middleware('permission:brands_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $categoryId = $request->integer('category_id');
        $brands = $this->brandRepository->query()->with('category')->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")
        )->when(
            $categoryId,
            fn($query) => $query->where('category_id', $categoryId)
        )->withCount('products')->paginate($perPage);
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

        $data['creatable_id'] = auth()->user()->id;
        $data['creatable_type'] = Admin::class;

        $brand = $this->brandRepository->store($data);
        if ($request->hasFile('logo')) {
            $brand->addMedia($request->file('logo'))
                ->usingName($brand->getTranslation('name', 'en'))
                ->toMediaCollection('brand_logo');
        }
        return response()->json(['data' => new BrandResource($brand)]);
    }

    public function show($brand): JsonResponse
    {
        $brand = $this->brandRepository->with(['category'])->find($brand);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return response()->json(['data' => new BrandShowResource($brand)]);
    }

    public function update(BrandUpdateRequest $request, $brand): JsonResponse
    {
        $data = $request->validated();
        $brandModel = $this->brandRepository->find($brand);

        if (!$brandModel) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        $data['creatable_id'] = auth()->user()->id;
        $data['creatable_type'] = Admin::class;
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
    //toggleIsActive
    public function toggleIsActive($brand): JsonResponse
    {
        $brand = $this->brandRepository->toggleIsActive($brand);
        if (!$brand) {
            return response()->json(['message' => 'Brand not found'], 404);
        }
        return response()->json(['data' => new BrandResource($brand)]);
    }
}
