<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\PointRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Point\PointProductPreviewResource;
use App\Http\Resources\Consumer\Point\VendorsPointListingResource;

class PointController extends ConsumerController
{
    public function __construct(
        protected VendorRepository $vendorRepository,
        protected PointRepository $pointRepository,
    ) {}



    public function index(Request $request): JsonResponse
    {

        $perPage = $request->integer('per_page', 15);
        $type = $request->string('type', 'earned');
        $vendors = $this->vendorRepository->query()
            ->select('vendors.id', 'vendors.store_name')
            ->withCount('followers')
            ->whereHas('points', function ($q) use ($type) {

                $q->active()
                    ->when($type, fn($q) => $q->where('type', $type));
            })
            ->with([
                'points' => function ($q) use ($type) {
                    $q->active()
                        ->when($type, fn($q) => $q->where('type', $type))
                        ->select('id', 'points', 'type', 'archived_at', 'vendor_id')
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
                'points.products' => function ($q) {
                    $q->B2BB2C()
                        ->limit(2);
                }
            ])
            ->paginate($perPage);


        return response()->json([
            'data' => VendorsPointListingResource::collection($vendors),
            'pagination' => [
                'currentPage' => $vendors->currentPage(),
                'total' => $vendors->total(),
                'perPage' => $vendors->perPage(),
                'lastPage' => $vendors->lastPage(),
                'hasMorePages' => $vendors->hasMorePages(),
            ],
        ]);



        // $type = $request->string('type', 'earned');
        // $perPage = $request->integer('per_page', 15);
        // $products = $this->productRepository->query()
        //     ->withWhereHas('points', function ($q) use ($type) {
        //         $q->where('type', $type)->active()->take(1);
        //     })
        //     ->B2BB2C()
        //     ->paginate($perPage);

        // return response()->json([
        //     'data' => VendorsPointListingResource::collection($products),
        //     'pagination' => [
        //         'currentPage' => $products->currentPage(),
        //         'total' => $products->total(),
        //         'perPage' => $products->perPage(),
        //         'lastPage' => $products->lastPage(),
        //         'hasMorePages' => $products->hasMorePages(),
        //     ],
        // ]);
    }
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

        $point = $this->pointRepository->query()
            ->active()
            ->where('id', $id)
            ->first();

        if (!$point) {
            return response()->json(['message' => 'Point not found'], 404);
        }

        $productsQuery = $point->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->B2BB2C()
            ->filter($filters);

        $products = $productsQuery->paginate($perPage);
        return response()->json([
            'data' => PointProductPreviewResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }
}
