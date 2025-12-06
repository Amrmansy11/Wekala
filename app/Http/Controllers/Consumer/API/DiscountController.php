<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Vendor\DiscountRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Discount\DiscountDetailsResource;
use App\Http\Resources\Consumer\Discount\VendorsDiscountListingResource;

class DiscountController extends ConsumerController
{
    public function __construct(
        protected DiscountRepository $discountRepository,
        protected VendorRepository $vendorRepository
    ) {
        parent::__construct();
    }

    /**
     * Get listing of all active discounts with products preview
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $vendors = $this->vendorRepository->query()
            ->select('vendors.id', 'vendors.store_name')
            ->withCount('followers')
            ->whereHas('discounts', function ($q) use ($search) {
                $q->active()
                    ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"));
            })
            ->with([
                'discounts' => function ($q) use ($search) {
                    $q->active()
                        ->when($search, fn($q) => $q->where('title', 'like', "%{$search}%"))
                        ->select('id', 'vendor_id', 'title', 'percentage')
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
                'discounts.products' => function ($q) {
                    $q->B2BB2C()
                        ->limit(6);
                }
            ])
            ->paginate($perPage);



        return response()->json([
            'data' => VendorsDiscountListingResource::collection($vendors),
            'pagination' => [
                'currentPage' => $vendors->currentPage(),
                'total' => $vendors->total(),
                'perPage' => $vendors->perPage(),
                'lastPage' => $vendors->lastPage(),
                'hasMorePages' => $vendors->hasMorePages(),
            ],
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
