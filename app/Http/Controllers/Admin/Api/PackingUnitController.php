<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PackingUnitResource;
use App\Http\Resources\PackingUnitShowResource;
use App\Repositories\Admin\PackingUnitRepository;
use App\Http\Requests\Admin\Api\PackingUnits\PackingUnitStoreRequest;
use App\Http\Requests\Admin\Api\PackingUnits\PackingUnitUpdateRequest;


class PackingUnitController extends AdminController
{
    public function __construct(protected PackingUnitRepository $packingUnitRepository)
    {
        $this->middleware('permission:packing_units_view')->only('index');
        $this->middleware('permission:packing_units_create')->only('store');
        $this->middleware('permission:packing_units_update')->only('update');
        $this->middleware('permission:packing_units_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $categoryId = $request->integer('category_id');
        $packingUnits = $this->packingUnitRepository->query()->with('category')->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")
        )->when(
            $categoryId,
            fn($query) => $query->where('category_id', $categoryId)
        )->paginate($perPage);
        return response()->json([
            'data' => PackingUnitResource::collection($packingUnits),
            'pagination' => [
                'currentPage' => $packingUnits->currentPage(),
                'total' => $packingUnits->total(),
                'perPage' => $packingUnits->perPage(),
                'lastPage' => $packingUnits->lastPage(),
                'hasMorePages' => $packingUnits->hasMorePages(),
            ]
        ]);
    }


    public function store(PackingUnitStoreRequest $request): JsonResponse
    {
        $packingUnit = $this->packingUnitRepository->store($request->validated());

        return response()->json(['data' => new PackingUnitResource($packingUnit)]);
    }

    public function show($packingUnit): JsonResponse
    {
        $packingUnit = $this->packingUnitRepository->query()->find($packingUnit);

        if (! $packingUnit) {
            return response()->json([
                'message' => 'Packing unit not found.',
            ], 404);
        }

        return response()->json(['data' => new PackingUnitShowResource($packingUnit)]);
    }

    public function update(PackingUnitUpdateRequest $request, $packingUnit): JsonResponse
    {
        $packingUnit = $this->packingUnitRepository->update($request->validated(), $packingUnit);
        return response()->json(['data' => new PackingUnitResource($packingUnit)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($packingUnit): JsonResponse
    {
        $packingUnit = $this->packingUnitRepository->delete($packingUnit);
        if (!$packingUnit) {
            return response()->json(['message' => 'Packing unit not found'], 404);
        }
        return response()->json(['data' => true]);
    }
    //toggleIsActive
    public function toggleIsActive($packingUnit): JsonResponse
    {
        $packingUnit = $this->packingUnitRepository->toggleIsActive($packingUnit);
        if (! $packingUnit) {
            return response()->json(['message' => 'Packing unit not found.'], 404);
        }
        return response()->json(['data' => new PackingUnitResource($packingUnit)]);
    }
}
