<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\GiftRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Gifts\GiftListingResource;

class GiftController extends ConsumerController
{
    public function __construct(
        protected GiftRepository $giftRepository
    ) {}

    /**
     * Get listing of all active gifts
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $query = $this->giftRepository
            ->queryWithRelations()
            ->whereHas('sourceProduct', function ($q) {
                $q->B2BB2C();
            })
            ->whereHas('giftProduct', function ($q) {
                $q->B2BB2C();
            })
            ->active();

        $gifts = $query->paginate($perPage);

        return response()->json([
            'data' => GiftListingResource::collection($gifts),
            'pagination' => [
                'currentPage' => $gifts->currentPage(),
                'total' => $gifts->total(),
                'perPage' => $gifts->perPage(),
                'lastPage' => $gifts->lastPage(),
                'hasMorePages' => $gifts->hasMorePages(),
            ]
        ]);
    }
}
