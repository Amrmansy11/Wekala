<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PolicyResource;
use App\Http\Resources\PolicyShowResource;
use App\Repositories\Admin\PolicyRepository;
use App\Http\Requests\Admin\Api\policies\PolicyStoreRequest;
use App\Http\Requests\Admin\Api\policies\PolicyUpdateRequest;


class PolicyController extends AdminController
{
    public function __construct(protected PolicyRepository $policyRepository)
    {
        $this->middleware('permission:policies_view')->only('index');
        $this->middleware('permission:policies_create')->only('store');
        $this->middleware('permission:policies_update')->only('update');
        $this->middleware('permission:policies_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $policies = $this->policyRepository->query()->paginate($perPage);
        return response()->json([
            'data' => PolicyResource::collection($policies),
            'pagination' => [
                'currentPage' => $policies->currentPage(),
                'total' => $policies->total(),
                'perPage' => $policies->perPage(),
                'lastPage' => $policies->lastPage(),
                'hasMorePages' => $policies->hasMorePages(),
            ]
        ]);
    }

    public function store(PolicyStoreRequest $request): JsonResponse
    {
        $policy = $this->policyRepository->store($request->validated());
        return response()->json(['data' => new PolicyResource($policy)]);
    }

    public function show($policy): JsonResponse
    {
        $policy = $this->policyRepository->find($policy);
        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }
        return response()->json(['data' => new PolicyShowResource($policy)]);
    }

    public function update(PolicyUpdateRequest $request, $policy): JsonResponse
    {

        $policy = $this->policyRepository->update($request->validated(), $policy);
        return response()->json(['data' => new PolicyResource($policy)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($policy): JsonResponse
    {
        $policy = $this->policyRepository->delete($policy);
        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }
        return response()->json(['data' => true]);
    }
}
