<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Product\ProductStoreRequest;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Repositories\Vendor\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        // $this->middleware('permission:vendor_products_view')->only('index');
        // $this->middleware('permission:vendor_products_create')->only('store');
        // $this->middleware('permission:vendor_products_update')->only('update');
        // $this->middleware('permission:vendor_products_delete')->only('destroy');
        $this->productRepository = $productRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status');

        $query = $this->productRepository->query()
            ->where('vendor_id', AppHelper::getVendorId())
            ->sellersOnly()
            ->with('category', 'brand', 'tags', 'sizes', 'variants', 'reviews')
            ->withCount('wishlists as favorites_count');

        // Apply status filter if provided
        if ($status) {
            if ($status === 'soon') {
                $query->where('published_at', '>', now());
            } elseif (in_array($status, \App\Enums\ProductStatus::toArray(), true)) {
                $query->where('status', $status);
            }
        }

        $products = $query->paginate($perPage);
        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $product = $this->productRepository->store($data);
        return response()->json(['message' => 'Product created successfully',
            'product' => new ProductDetailsResource($product)], 201);
    }

    public function show($id): JsonResponse
    {
        $product = $this->productRepository->show($id);
        return response()->json(['data' => new ProductDetailsResource($product)]);
    }
}
