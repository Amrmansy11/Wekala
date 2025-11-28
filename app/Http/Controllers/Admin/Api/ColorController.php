<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\ColorResource;
use App\Http\Resources\ColorShowResource;
use App\Repositories\Admin\ColorRepository;
use App\Http\Requests\Admin\Api\Colors\ColorStoreRequest;
use App\Http\Requests\Admin\Api\Colors\ColorUpdateRequest;


class ColorController extends AdminController
{
    public function __construct(protected ColorRepository $colorRepository)
    {
        $this->middleware('permission:colors_view')->only('index');
        $this->middleware('permission:colors_create')->only('store');
        $this->middleware('permission:colors_update')->only('update');
        $this->middleware('permission:colors_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $colors = $this->colorRepository->query()->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")->orWhere('hex_code', 'like', "%{$search}%")
        )->withCount('products')->paginate($perPage);
        return response()->json([
            'data' => ColorResource::collection($colors),
            'pagination' => [
                'currentPage' => $colors->currentPage(),
                'total' => $colors->total(),
                'perPage' => $colors->perPage(),
                'lastPage' => $colors->lastPage(),
                'hasMorePages' => $colors->hasMorePages(),
            ]
        ]);
    }


    public function store(ColorStoreRequest $request): JsonResponse
    {
        $color = $this->colorRepository->store($request->validated());

        return response()->json(['data' => new ColorResource($color)]);
    }

    public function show($color): JsonResponse
    {
        $color = $this->colorRepository->query()->find($color);

        if (! $color) {
            return response()->json([
                'message' => 'Color not found.',
            ], 404);
        }

        return response()->json(['data' => new ColorShowResource($color)]);
    }

    public function update(ColorUpdateRequest $request, $color): JsonResponse
    {
        $color = $this->colorRepository->update($request->validated(), $color);
        return response()->json(['data' => new ColorResource($color)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($color): JsonResponse
    {
        $color = $this->colorRepository->delete($color);
        if (!$color) {
            return response()->json(['message' => 'Color not found.'], 404);
        }
        return response()->json(['data' => true]);
    }
    public function toggleIsActive($color): JsonResponse
    {
        $color = $this->colorRepository->toggleIsActive($color);
        if (! $color) {
            return response()->json(['message' => 'Color not found.'], 404);
        }
        return response()->json(['data' => new ColorResource($color)]);
    }
}
