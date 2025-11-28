<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\AppHelper;
use App\Http\Requests\Admin\Api\Product\ProductStoreRequest;
use App\Http\Requests\Admin\Api\Product\ProductUpdateRequest;
use App\Http\Resources\ProductDetailsResource;
use App\Http\Resources\ProductResource;
use App\Repositories\Vendor\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ProductController extends AdminController
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status');

        $query = $this->productRepository->query()
            ->with('category', 'brand', 'tags', 'sizes', 'variants');

        // Apply status filter if provided
        if ($status) {
            if ($status === 'soon') {
                $query->where('published_at', '>', now());
            } elseif (in_array($status, \App\Enums\ProductStatus::toArray(), true)) {
                $query->where('status', $status);
            }
        }
        if ($request->has('poilcy')) {
            if ($request->get('poilcy') == 'yes') {
                $query->where('elwekala_policy', 1);
            } elseif ($request->get('poilcy') == 'no') {
                $query->where('elwekala_policy', 0);
            }
        }
        if ($request->has('search')) {
            $keyword = $request->get('search');
            $query->where('name', 'like', "%$keyword%");
        }
        if ($request->has('vendor_id')) {
            $vendor_id = $request->get('vendor_id');
            $query->where('vendor_id',$vendor_id);
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
        $this->productRepository->store($data);
        return response()->json(['message' => 'Product created successfully'], 201);
    }

    public function show($id): JsonResponse
    {
        $product = $this->productRepository->show($id);
        return response()->json(['data' => new ProductDetailsResource($product)]);
    }

    public function update(ProductUpdateRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $product = $this->productRepository->update($data, $id);
        return response()->json([
            'message' => 'Product updated successfully',
            'data' => new ProductDetailsResource($product)
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->productRepository->delete($id);
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
