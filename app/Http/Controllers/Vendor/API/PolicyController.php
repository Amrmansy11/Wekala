<?php

namespace App\Http\Controllers\Vendor\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\PolicyResource;
use App\Repositories\Vendor\PolicyRepository;
use App\Http\Controllers\Vendor\API\VendorController;

class PolicyController extends VendorController
{

    public function __construct(protected PolicyRepository $policyRepository) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $policies = $this->policyRepository->query()
            ->paginate($perPage);
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

    public function show($policy): JsonResponse
    {
        $policy = $this->policyRepository->find($policy);
        if (!$policy) {
            return response()->json(['message' => 'Policy not found'], 404);
        }
        return response()->json(['data' => new PolicyResource($policy)]);
    }
}
