<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DeliveryAreaResource;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Repositories\Vendor\DeliveryAreaRepository;
use App\Http\Requests\Admin\Api\DeliveryArea\DeliveryAreaStoreRequest;
use App\Http\Requests\Admin\Api\DeliveryArea\DeliveryAreaUpdateRequest;

class DeliveryAreaController extends AdminController
{
    public function __construct(protected DeliveryAreaRepository $deliveryAreaRepository)
    {
        $this->middleware('permission:delivery_areas_view')->only('index');
        $this->middleware('permission:delivery_areas_create')->only('store');
        $this->middleware('permission:delivery_areas_update')->only('update');
        $this->middleware('permission:delivery_areas_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search', null);
        $vendorId = $request->integer('vendor_id', null);

        $deliveryAreas = $this->deliveryAreaRepository->query()
            ->search($search)
            ->when($vendorId, fn($query) => $query->where('vendor_id', $vendorId))
            ->with('state', 'city')
            ->paginate($perPage);
        return response()->json([
            'data' => DeliveryAreaResource::collection($deliveryAreas),
            'pagination' => [
                'currentPage' => $deliveryAreas->currentPage(),
                'total' => $deliveryAreas->total(),
                'perPage' => $deliveryAreas->perPage(),
                'lastPage' => $deliveryAreas->lastPage(),
                'hasMorePages' => $deliveryAreas->hasMorePages(),
            ]
        ]);
    }



    public function store(DeliveryAreaStoreRequest $request): JsonResponse
    {
        $deliveryArea = $this->deliveryAreaRepository->store($request->validated());

        return response()->json(['data' => new DeliveryAreaResource($deliveryArea)], 201);
    }


    public function update(DeliveryAreaUpdateRequest $request, int $id): JsonResponse
    {
        $deliveryArea = $this->deliveryAreaRepository->find($id);

        if (!$deliveryArea) {
            return response()->json(['message' => 'Delivery Area not found'], 404);
        }
        $deliveryArea->update($request->validated());

        return response()->json(['data' => new DeliveryAreaResource($deliveryArea)]);
    }


    /**
     * @throws Exception
     */
    public function destroy($deliveryArea): JsonResponse
    {
        $deliveryArea = $this->deliveryAreaRepository->delete($deliveryArea);
        if (!$deliveryArea) {
            return response()->json(['message' => 'Delivery Area not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
