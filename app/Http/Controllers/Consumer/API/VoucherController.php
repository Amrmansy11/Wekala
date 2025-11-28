<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\VoucherRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Voucher\VoucherDetailsResource;
use App\Http\Resources\Consumer\Voucher\VoucherListingResource;
use App\Http\Resources\Consumer\Voucher\VoucherProductResource;

class VoucherController extends ConsumerController
{
    public function __construct(
        protected VoucherRepository $voucherRepository
    ) {}

    /**
     * Get listing of all active offers
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $name = $request->get('name');
        $query = $this->voucherRepository->query()
            ->with('products')
            ->wherehas('products', function ($q) {
                $q->B2BB2C();
            })
            ->when($name, function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            })
            ->status('active');
        $vouchers = $query->paginate($perPage);

        return response()->json([
            'data' => VoucherListingResource::collection($vouchers),
            'pagination' => [
                'currentPage' => $vouchers->currentPage(),
                'total' => $vouchers->total(),
                'perPage' => $vouchers->perPage(),
                'lastPage' => $vouchers->lastPage(),
                'hasMorePages' => $vouchers->hasMorePages(),
            ]
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
            'data' => new VoucherDetailsResource($voucher),
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
