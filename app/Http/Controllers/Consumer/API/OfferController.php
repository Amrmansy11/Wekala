<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\OfferRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Offer\OfferDetailsResource;
use App\Http\Resources\Consumer\Offer\OfferProductResource;
use App\Http\Resources\Consumer\Offer\VendorsOfferListingResource;

class OfferController extends ConsumerController
{
    public function __construct(
        protected VendorRepository $vendorRepository,
        protected OfferRepository $offerRepository
    ) {}

    /**
     * Get listing of all active offers
     */
    public function index(Request $request): JsonResponse
    {

        $type = $request->get('type');
        $perPage = $request->integer('per_page', 15);
        $vendors = $this->vendorRepository->query()
            ->select('vendors.id', 'vendors.store_name')
            ->withCount('followers')
            ->whereHas('offers', function ($q) use ($type) {
                $q->status('active')
                    ->when($type, fn($q) => $q->where('type', $type))
                    ->whereHas('products');
            })
            ->with([
                'offers' => function ($q) use ($type) {
                    $q
                        ->when($type, fn($q) => $q->where('type', $type))
                        ->orderBy('created_at', 'desc')
                        ->limit(10);
                },
                'offers.products' => function ($q) {
                    $q->B2BB2C()
                        ->limit(3);
                }
            ])
            ->paginate($perPage);
        return response()->json([
            'data' => VendorsOfferListingResource::collection($vendors),
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

        $offer = $this->offerRepository->query()
            ->status('active')
            ->where('id', $id)
            ->first();

        if (!$offer) {
            return response()->json(['message' => 'Offer not found'], 404);
        }

        $productsQuery = $offer->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->B2BB2C()
            ->filter($filters);

        $products = $productsQuery->paginate($perPage);

        return response()->json([
            'offer' => new OfferDetailsResource($offer),
            'products' => [
                'data' => OfferProductResource::collection($products),
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
