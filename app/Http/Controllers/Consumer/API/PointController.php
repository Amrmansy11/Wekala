<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\PointRepository;
use App\Repositories\Vendor\ProductRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Point\PointListingResource;
use App\Http\Resources\Consumer\Point\PointProductPreviewResource;

class PointController extends ConsumerController
{
    public function __construct(
        protected ProductRepository $productRepository,
        // protected PointRepository $pointRepository
    ) {}

    /**
     * Get listing of all active discounts with products preview
     */
    // public function index(Request $request): JsonResponse
    // {
    //     $type = $request->string('type', 'earned');


    //     $query = $this->pointRepository->query()
    //         ->active()
    //         ->with(['products' => function ($query) {
    //             $query->take(1); // Preview 1 product for listing
    //         }])
    //         ->has('products')
    //         ->when($type, function ($q) use ($type) {
    //             $q->where('type', $type);
    //         });

    //     $points = $query->get();

    //     return response()->json([
    //         'data' => PointListingResource::collection($points),
    //     ]);
    // }

    public function index(Request $request): JsonResponse
    {
        $type = $request->string('type', 'earned');
        $perPage = $request->integer('per_page', 15);
        $products = $this->productRepository->query()
            ->withWhereHas('points', function ($q) use ($type) {
                $q->where('type', $type)->active()->take(1);
            })
            ->B2BB2C()
            ->paginate($perPage);

        return response()->json([
            'data' => PointProductPreviewResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
        ]);
    }
}
