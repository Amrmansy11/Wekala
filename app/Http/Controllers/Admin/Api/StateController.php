<?php

namespace App\Http\Controllers\Admin\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\StatesResource;
use App\Http\Resources\StatesShowResource;
use App\Repositories\Admin\StateRepository;
use App\Http\Controllers\Admin\Api\AdminController;
use App\Http\Requests\Admin\Api\States\StateStoreRequest;
use App\Http\Requests\Admin\Api\States\StateUpdateRequest;


class StateController extends AdminController
{
    public function __construct(protected StateRepository $stateRepository)
    {
        $this->middleware('permission:states_view')->only('index');
        $this->middleware('permission:states_create')->only('store');
        $this->middleware('permission:states_update')->only('update');
        $this->middleware('permission:states_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $states = $this->stateRepository->query()->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")
        )->paginate($perPage);
        return response()->json([
            'data' => StatesResource::collection($states),
            'pagination' => [
                'currentPage' => $states->currentPage(),
                'total' => $states->total(),
                'perPage' => $states->perPage(),
                'lastPage' => $states->lastPage(),
                'hasMorePages' => $states->hasMorePages(),
            ]
        ]);
    }

    public function store(StateStoreRequest $request): JsonResponse
    {
        $state = $this->stateRepository->store($request->validated());
        return response()->json(['data' => new StatesResource($state)]);
    }

    public function show($state): JsonResponse
    {
        $state = $this->stateRepository->find($state);
        if (!$state) {
            return response()->json(['message' => 'State not found'], 404);
        }
        return response()->json(['data' => new StatesShowResource($state)]);
    }

    public function update(StateUpdateRequest $request, $state): JsonResponse
    {
        $state = $this->stateRepository->update($request->validated(), $state);
        return response()->json(['data' => new StatesResource($state)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($state): JsonResponse
    {
        $state = $this->stateRepository->delete($state);
        if (!$state) {
            return response()->json(['message' => 'State not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
