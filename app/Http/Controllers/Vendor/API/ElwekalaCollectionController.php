<?php

namespace App\Http\Controllers\Vendor\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ElwekalaCollectionResource;
use App\Http\Controllers\Vendor\API\VendorController;
use App\Repositories\Vendor\ElwekalaCollectionRepository;

class ElwekalaCollectionController extends VendorController
{

    public function __construct(protected ElwekalaCollectionRepository $elwekalaCollectionRepository) {}

    public function index(Request $request): JsonResponse
    {
        $type = $request->string('type', 'best_sellers');
        $perPage = $request->integer('per_page', 15);
        $elwekalaCollections = $this->elwekalaCollectionRepository->query()
            ->whereNotNull('type')
            ->where('type', $type)
            ->withWhereHas('product')->paginate($perPage);
        return response()->json([
            'data' => ElwekalaCollectionResource::collection($elwekalaCollections),
            'pagination' => [
                'currentPage' => $elwekalaCollections->currentPage(),
                'total' => $elwekalaCollections->total(),
                'perPage' => $elwekalaCollections->perPage(),
                'lastPage' => $elwekalaCollections->lastPage(),
                'hasMorePages' => $elwekalaCollections->hasMorePages(),
            ]
        ]);
    }
}
