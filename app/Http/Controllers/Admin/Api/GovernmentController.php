<?php

namespace App\Http\Controllers\Admin\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\GovernmentResource;
use App\Http\Resources\GovernmentShowResource;
use App\Repositories\Admin\GovernmentRepository;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Http\Requests\Admin\Api\Governments\GovernmentStoreRequest;
use App\Http\Requests\Admin\Api\Governments\GovernmentUpdateRequest;


class GovernmentController extends AdminController
{
    public function __construct(protected GovernmentRepository $governmentRepository)
    {
        $this->middleware('permission:governments_view')->only('index');
        $this->middleware('permission:governments_create')->only('store');
        $this->middleware('permission:governments_update')->only('update');
        $this->middleware('permission:governments_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $cities = $this->governmentRepository->query()->with('city')->has('city')->paginate($perPage);
        return response()->json([
            'data' => GovernmentResource::collection($cities),
            'pagination' => [
                'currentPage' => $cities->currentPage(),
                'total' => $cities->total(),
                'perPage' => $cities->perPage(),
                'lastPage' => $cities->lastPage(),
                'hasMorePages' => $cities->hasMorePages(),
            ]
        ]);
    }

    public function store(GovernmentStoreRequest $request): JsonResponse
    {
        $government = $this->governmentRepository->store($request->validated());
        return response()->json(['data' => new GovernmentResource($government)], 201);
    }

    public function show($government): JsonResponse
    {
        $government = $this->governmentRepository->with(['city'])->find($government);
        if (!$government) {
            return response()->json(['message' => 'government not found'], 404);
        }
        return response()->json(['data' => new GovernmentShowResource($government)]);
    }

    public function update(GovernmentUpdateRequest $request, $city): JsonResponse
    {
        $government = $this->governmentRepository->update($request->validated(), $government);
        return response()->json(['data' => new GovernmentResource($government)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($government): JsonResponse
    {
        $government = $this->governmentRepository->delete($government);
        if (!$government) {
            return response()->json(['message' => 'government not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
