<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\OfferRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Offer\OfferDetailsResource;
use App\Http\Resources\Consumer\Offer\OfferListingResource;
use App\Http\Resources\Consumer\Offer\OfferProductResource;

class OfferController extends ConsumerController
{
    public function __construct(
        protected OfferRepository $offerRepository
    ) {}

    /**
     * Get listing of all active offers
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $name = $request->get('name');

        $query = $this->offerRepository->query()
            ->with(['products' => function ($query) {
                $query->take(3); // Preview 3 products for listing
            }])
            ->wherehas('products', function ($q) {
                $q->B2BB2C();
            })
            ->status('active')
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })->when($name, function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        $offers = $query->get();
        return response()->json([
            'data' => OfferListingResource::collection($offers),
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
            // 'data' => new OfferDetailsResource($offer),
            'data' => OfferProductResource::collection($products),
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
