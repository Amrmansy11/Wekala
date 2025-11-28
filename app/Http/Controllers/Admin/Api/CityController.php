<?php

namespace App\Http\Controllers\Admin\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CityResource;
use App\Http\Resources\CityShowResource;
use App\Repositories\Admin\CityRepository;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Http\Requests\Admin\Api\Cities\CityStoreRequest;
use App\Http\Requests\Admin\Api\Cities\CityUpdateRequest;


class CityController extends AdminController
{
    public function __construct(protected CityRepository $cityRepository)
    {
        $this->middleware('permission:cities_view')->only('index');
        $this->middleware('permission:cities_create')->only('store');
        $this->middleware('permission:cities_update')->only('update');
        $this->middleware('permission:cities_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $stateId = $request->integer('state_id');
        $cities = $this->cityRepository->query()->with('state')->has('state')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->when($stateId, function ($query) use ($stateId) {
                $query->where('state_id', $stateId);
            })->paginate($perPage);
        return response()->json([
            'data' => CityResource::collection($cities),
            'pagination' => [
                'currentPage' => $cities->currentPage(),
                'total' => $cities->total(),
                'perPage' => $cities->perPage(),
                'lastPage' => $cities->lastPage(),
                'hasMorePages' => $cities->hasMorePages(),
            ]
        ]);
    }

    public function store(CityStoreRequest $request): JsonResponse
    {
        $city = $this->cityRepository->store($request->validated());
        return response()->json(['data' => new CityResource($city)]);
    }

    public function show($city): JsonResponse
    {
        $city = $this->cityRepository->with(['state'])->find($city);
        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }
        return response()->json(['data' => new CityShowResource($city)]);
    }

    public function update(CityUpdateRequest $request, $city): JsonResponse
    {
        $city = $this->cityRepository->update($request->validated(), $city);
        return response()->json(['data' => new CityResource($city)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($city): JsonResponse
    {
        $city = $this->cityRepository->delete($city);
        if (!$city) {
            return response()->json(['message' => 'City not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
