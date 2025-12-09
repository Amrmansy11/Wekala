<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Vendor\VoucherRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Voucher\VoucherDetailsResource;
use App\Http\Resources\Consumer\Voucher\VoucherListingResource;
use App\Http\Resources\Consumer\Voucher\VoucherProductResource;
use App\Http\Resources\Consumer\Voucher\VendorsVoucherListingResource;

class VoucherController extends ConsumerController
{
    public function __construct(
        protected VendorRepository $vendorRepository,
        protected VoucherRepository $voucherRepository
    ) {}

    /**
     * Get listing of all active offers
     */

    public function index(Request $request): JsonResponse
    {

        $name = $request->get('name');
        $perPage = $request->integer('per_page', 15);
        $vendors = $this->vendorRepository->query()
            ->select('vendors.id', 'vendors.store_name')
            ->withCount('followers')
            ->whereHas('vouchers', function ($q) use ($name) {
                $q->status('active')
                    ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
                    ->whereHas('products');
            })
            ->with([
                'vouchers' => function ($q) use ($name) {
                    $q
                        ->when($name, fn($q) => $q->where('name', 'like', "%{$name}%"))
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
                'vouchers.products' => function ($q) {
                    $q->B2BB2C()
                        ->limit(3);
                }
            ])
            ->paginate($perPage);
        return response()->json([
            'data' => VendorsVoucherListingResource::collection($vendors),
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

        $voucher = $this->voucherRepository->query()
            ->status('active')
            ->where('id', $id)
            ->first();

        if (!$voucher) {
            return response()->json(['message' => 'Voucher not found'], 404);
        }

        $productsQuery = $voucher->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->B2BB2C()
            ->filter($filters);

        $products = $productsQuery->paginate($perPage);
        return response()->json([
            'data' => new VoucherListingResource($voucher),
            'products' => [
                'data' => VoucherProductResource::collection($products),
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
