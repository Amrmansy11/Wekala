<?php

namespace App\Http\Controllers\Consumer\API;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Home\VendorResource;
use App\Http\Resources\Home\ProductResource;
use App\Http\Controllers\Consumer\API\ConsumerController;

class SearchController extends ConsumerController
{
    /**
     * Global search across products and vendors
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $keyword = $request->string('keyword', '');
        $perPage = $request->integer('per_page', 15);

        if (empty($keyword)) {
            return response()->json([
                'data' => [
                    'products' => [],
                    'vendors' => []
                ],
                'pagination' => [
                    'currentPage' => 1,
                    'total' => 0,
                    'perPage' => $perPage,
                    'lastPage' => 1,
                    'hasMorePages' => false,
                ]
            ]);
        }

        // Search products by name
        $products = Product::query()
            ->where('name', 'like', "%{$keyword}%")
            ->B2BB2C()
            ->with(['vendor', 'category', 'brand'])
            ->paginate($perPage);

        // Search vendors by store_name (JSON translatable field)
        $vendors = Vendor::query()
            ->whereAny(['store_name->ar', 'store_name->en'], 'like', "%{$keyword}%")
            ->where('status', 'approved')
            ->with(['category', 'state', 'city'])
            ->paginate($perPage);

        return response()->json([
            'data' => [
                'products' => ProductResource::collection($products),
                'vendors' => VendorResource::collection($vendors)
            ],
            'pagination' => [
                'products' => [
                    'currentPage' => $products->currentPage(),
                    'total' => $products->total(),
                    'perPage' => $products->perPage(),
                    'lastPage' => $products->lastPage(),
                    'hasMorePages' => $products->hasMorePages(),
                ],
                'vendors' => [
                    'currentPage' => $vendors->currentPage(),
                    'total' => $vendors->total(),
                    'perPage' => $vendors->perPage(),
                    'lastPage' => $vendors->lastPage(),
                    'hasMorePages' => $vendors->hasMorePages(),
                ]
            ]
        ]);
    }
}
