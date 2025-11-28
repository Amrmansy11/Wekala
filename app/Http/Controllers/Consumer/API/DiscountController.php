<?php

namespace App\Http\Controllers\Consumer\API;

use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Discount\DiscountListingResource;
use App\Http\Resources\Consumer\Discount\DiscountDetailsResource;
use App\Repositories\Vendor\DiscountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscountController extends ConsumerController
{
    public function __construct(
        protected DiscountRepository $discountRepository
    ) {}

    /**
     * Get listing of all active discounts with products preview
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search');

        $query = $this->discountRepository->query()
            ->active()
            ->with(['products' => function ($query) {
                $query->take(3); // Preview 3 products for listing
            }])
            ->wherehas('products', function ($q) {
                $q->B2BB2C();
            })
            ->when($search, function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });

        $discounts = $query->get();

        return response()->json([
            'data' => DiscountListingResource::collection($discounts),
        ]);
    }

    /**
     * Get discount details with all products
     */
    public function show($id, Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        $filters = [
            'search' => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id' => $request->array('size_id', []),
            'color_id' => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id' => $request->array('tag_id', []),
        ];

        $discount = $this->discountRepository->query()
            ->active()
            ->where('id', $id)
            ->first();

        if (!$discount) {
            return response()->json(['message' => 'Discount not found'], 404);
        }

        $productsQuery = $discount->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->B2BB2C()
            ->filter($filters);

        $products = $productsQuery->paginate($perPage);

        return response()->json([
            'data' => new DiscountDetailsResource($discount),
            'products' => [
                'data' => \App\Http\Resources\Consumer\Discount\DiscountProductResource::collection($products),
                'pagination' => [
                    'currentPage' => $products->currentPage(),
                    'total' => $products->total(),
                    'perPage' => $products->perPage(),
                    'lastPage' => $products->lastPage(),
                    'hasMorePages' => $products->hasMorePages(),
                ],
            ],
        ]);
    }
}
