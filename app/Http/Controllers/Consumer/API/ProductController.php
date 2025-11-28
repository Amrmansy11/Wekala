<?php

namespace App\Http\Controllers\Consumer\API;

use App\Http\Resources\Consumer\Store\StoreProductsResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\ProductRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Products\ProductDetailsResource;

class ProductController extends ConsumerController
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function index(Request $request): JsonResponse
    {

        $perPage = $request->integer('per_page', 15);
        $filters = [
            'search' => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id' => $request->array('size_id', []),
            'color_id' => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id' => $request->array('tag_id', []),
            'brand_id' => $request->array('brand_id', [])
        ];
        $products = Product::query()
            ->with(['points' => function ($query) {
                $query->whereNull('archived_at');
            }, 'discounts' => function ($query) {
                $query->whereNull('archived_at');
            }])
            ->filter($filters)
            ->B2BB2C()
            ->paginate($perPage);
        return response()->json([
            'data' => StoreProductsResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }



    public function show($id): JsonResponse
    {
        $product = $this->productRepository->show($id, 'consumer-api', 'b2b_b2c');
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json(['data' => new ProductDetailsResource($product)]);
    }
}
