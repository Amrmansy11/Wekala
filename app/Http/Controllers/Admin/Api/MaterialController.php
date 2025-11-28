<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\MaterialResource;
use App\Http\Resources\MaterialShowResource;
use App\Repositories\Admin\MaterialRepository;
use App\Http\Requests\Admin\Api\Materials\MaterialStoreRequest;
use App\Http\Requests\Admin\Api\Materials\MaterialUpdateRequest;


class MaterialController extends AdminController
{
    public function __construct(protected MaterialRepository $materialRepository)
    {
        $this->middleware('permission:materials_view')->only('index');
        $this->middleware('permission:materials_create')->only('store');
        $this->middleware('permission:materials_update')->only('update');
        $this->middleware('permission:materials_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $materials = $this->materialRepository->query()->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")
        )->withCount('products')->paginate($perPage);
        return response()->json([
            'data' => MaterialResource::collection($materials),
            'pagination' => [
                'currentPage' => $materials->currentPage(),
                'total' => $materials->total(),
                'perPage' => $materials->perPage(),
                'lastPage' => $materials->lastPage(),
                'hasMorePages' => $materials->hasMorePages(),
            ]
        ]);
    }

    public function store(MaterialStoreRequest $request): JsonResponse
    {
        $material = $this->materialRepository->store($request->validated());
        return response()->json(['data' => new MaterialResource($material)]);
    }

    public function show($material): JsonResponse
    {
        $material = $this->materialRepository->find($material);
        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }
        return response()->json(['data' => new MaterialShowResource($material)]);
    }

    public function update(MaterialUpdateRequest $request, $material): JsonResponse
    {
        $material = $this->materialRepository->update($request->validated(), $material);
        return response()->json(['data' => new MaterialResource($material)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($material): JsonResponse
    {
        $material = $this->materialRepository->delete($material);
        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }
        return response()->json(['data' => true]);
    }
    //toggleIsActive
    public function toggleIsActive($material): JsonResponse
    {
        $material = $this->materialRepository->toggleIsActive($material);
        if (!$material) {
            return response()->json(['message' => 'Material not found'], 404);
        }
        return response()->json(['data' => new MaterialResource($material)]);
    }
}
