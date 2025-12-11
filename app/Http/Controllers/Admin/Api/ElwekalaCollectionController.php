<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\Admin\ElwekalaCollectionResource;
use App\Repositories\Admin\ElwekalaCollectionRepository;
use App\Http\Resources\Admin\ElwekalaCollectionShowResource;
use App\Http\Requests\Admin\Api\ElwekalaCollection\ElwekalaCollectionStoreRequest;
use App\Http\Requests\Admin\Api\ElwekalaCollection\ElwekalaCollectionUpdateRequest;


class ElwekalaCollectionController extends AdminController
{
    public function __construct(protected ElwekalaCollectionRepository $elwekalaCollectionRepository)
    {
        $this->middleware('permission:elwekala_collections_view')->only('index');
        $this->middleware('permission:elwekala_collections_create')->only('store');
        $this->middleware('permission:elwekala_collections_update')->only('update');
        $this->middleware('permission:elwekala_collections_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $type = $request->string('type', 'best_sellers');
        $type_elwekala = $request->string('type_elwekala', 'seller');
        $perPage = $request->integer('per_page', 15);
        $elwekalaCollections = $this->elwekalaCollectionRepository->query()
            ->whereNotNull('type')
            ->where('type', $type)
            ->where('type_elwekala', $type_elwekala)
            ->withWhereHas('product', fn($query) => $query->with('variants'))->paginate($perPage);
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

    public function store(ElwekalaCollectionStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $createdItems = [];

        foreach ($validated['product_id'] as $productId) {
            $createdItems[] = $this->elwekalaCollectionRepository->store([
                'type' => $validated['type'],
                'product_id' => $productId,
                'type_elwekala' => $validated['type_elwekala'],
            ]);
        }

        return response()->json([
            'data' => null,
            'message' => 'Collection created successfully',
        ]);
    }

    public function show($elwekalaCollection): JsonResponse
    {
        $elwekalaCollection = $this->elwekalaCollectionRepository->find($elwekalaCollection);
        if (!$elwekalaCollection) {
            return response()->json(['message' => 'Elwekala Collection not found'], 404);
        }
        return response()->json(['data' => new ElwekalaCollectionShowResource($elwekalaCollection)]);
    }

    public function update(ElwekalaCollectionUpdateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->elwekalaCollectionRepository->query()->where('type', $validated['type'])->where('type_elwekala', $validated['type_elwekala'])->delete();

        $createdItems = [];

        foreach ($validated['product_id'] as $productId) {
            $createdItems[] = $this->elwekalaCollectionRepository->store([
                'type'       => $validated['type'],
                'product_id' => $productId,
                'type_elwekala' => $validated['type_elwekala'],
            ]);
        }
        return response()->json([
            'data'    => null,
            'message' => 'Collection updated successfully',
        ]);
    }


    /**
     * @throws Exception
     */
    public function destroy($type, $type_elwekala): JsonResponse
    {
        $allowedTypes = ['feeds', 'best_sellers', 'new_arrivals', 'most_popular', 'flash_sale'];

        if (!in_array($type, $allowedTypes, true)) {
            return response()->json(['message' => 'Invalid type'], 422);
        }

        $deleted = $this->elwekalaCollectionRepository
            ->query()
            ->where('type', $type)
            ->where('type_elwekala', $type_elwekala)
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Elwekala Collection not found'], 404);
        }

        return response()->json(['data' => true]);
    }
}
