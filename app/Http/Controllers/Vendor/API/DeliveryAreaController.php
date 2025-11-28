<?php

namespace App\Http\Controllers\Vendor\API;

use Exception;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DeliveryAreaResource;
use App\Repositories\Vendor\DeliveryAreaRepository;
use App\Http\Requests\Vendor\Api\DeliveryArea\DeliveryAreaStoreRequest;
use App\Http\Requests\Vendor\Api\DeliveryArea\DeliveryAreaUpdateRequest;

class DeliveryAreaController extends VendorController
{
    public function __construct(protected DeliveryAreaRepository $deliveryAreaRepository)
    {
        // $this->middleware('permission:vendor_delivery_areas_view')->only('index');
        // $this->middleware('permission:vendor_delivery_areas_create')->only('store');
        // $this->middleware('permission:vendor_delivery_areas_update')->only('update');
        // $this->middleware('permission:vendor_delivery_areas_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search', null);

        $deliveryAreas = $this->deliveryAreaRepository->query()
            ->search($search)
            ->where('vendor_id', AppHelper::getVendorId())
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
