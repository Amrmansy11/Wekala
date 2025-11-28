<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SizeResource;
use App\Http\Resources\SizeShowResource;
use App\Repositories\Admin\SizeRepository;
use App\Http\Requests\Admin\Api\Size\SizeStoreRequest;
use App\Http\Requests\Admin\Api\Size\SizeUpdateRequest;


class SizeController extends AdminController
{
    public function __construct(protected SizeRepository $sizeRepository)
    {
        $this->middleware('permission:sizes_view')->only('index');
        $this->middleware('permission:sizes_create')->only('store');
        $this->middleware('permission:sizes_update')->only('update');
        $this->middleware('permission:sizes_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $categoryId = $request->integer('category_id');
        $sizes = $this->sizeRepository->query()
            ->when(
                $search,
                fn($query) => $query->where('name', 'like', "%{$search}%")
            )
            ->when(
                $categoryId,
                fn($query) => $query->where('category_id', $categoryId)
            )
            ->with('category')->paginate($perPage);
        return response()->json([
            'data' => SizeResource::collection($sizes),
            'pagination' => [
                'currentPage' => $sizes->currentPage(),
                'total' => $sizes->total(),
                'perPage' => $sizes->perPage(),
                'lastPage' => $sizes->lastPage(),
                'hasMorePages' => $sizes->hasMorePages(),
            ]
        ]);
    }


    public function store(SizeStoreRequest $request): JsonResponse
    {
        $size = $this->sizeRepository->store($request->validated());

        return response()->json(['data' => new SizeResource($size)]);
    }

    public function show($size): JsonResponse
    {
        $size = $this->sizeRepository->query()->find($size);

        if (! $size) {
            return response()->json([
                'message' => 'Size not found.',
            ], 404);
        }

        return response()->json(['data' => new SizeShowResource($size)]);
    }

    public function update(SizeUpdateRequest $request, $size): JsonResponse
    {
        $size = $this->sizeRepository->update($request->validated(), $size);
        return response()->json(['data' => new SizeResource($size)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($size): JsonResponse
    {
        $size = $this->sizeRepository->delete($size);
        if (! $size) {
            return response()->json(['message' => 'Size not found.'], 404);
        }
        return response()->json(['data' => true]);
    }
    //toggleIsActive
    public function toggleIsActive($size): JsonResponse
    {
        $size = $this->sizeRepository->toggleIsActive($size);
        if (! $size) {
            return response()->json(['message' => 'Size not found.'], 404);
        }
        return response()->json(['data' => new SizeResource($size)]);
    }
}
