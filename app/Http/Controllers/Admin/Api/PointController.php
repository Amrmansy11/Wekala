<?php

namespace App\Http\Controllers\Admin\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Point\PointStoreRequest;
use App\Http\Resources\PointResource;
use App\Repositories\Vendor\PointRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointController extends AdminController
{
    protected PointRepository $pointRepository;

    public function __construct(PointRepository $pointRepository)
    {
//        $this->middleware('permission:vendor_points_view')->only('index');
//        $this->middleware('permission:vendor_points_create')->only('store');
//        $this->middleware('permission:vendor_points_update')->only('update');
//        $this->middleware('permission:vendor_points_delete')->only('destroy');

        parent::__construct();
        $this->pointRepository = $pointRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $type = $request->get('type');
        $status = $request->get('status'); // 'active', 'archived', or null for all

        $query = $this->pointRepository->query()
            ->with('products')
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->when($status === 'active', function ($q) {
                $q->active();
            })
            ->when($status === 'archived', function ($q) {
                $q->archived();
            });

        $points = $query->paginate($perPage);

        return response()->json([
            'data' => PointResource::collection($points),
            'pagination' => [
                'currentPage' => $points->currentPage(),
                'total' => $points->total(),
                'perPage' => $points->perPage(),
                'lastPage' => $points->lastPage(),
                'hasMorePages' => $points->hasMorePages(),
            ]
        ]);
    }

    public function store(PointStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->pointRepository->store($data);
        return response()->json(['message' => 'Point created successfully'], 201);
    }

    public function show($id): JsonResponse
    {
        $point = $this->pointRepository->show($id);
        return response()->json(['data' => new PointResource($point)]);
    }

    public function update(PointStoreRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $this->pointRepository->update($id, $data);
        return response()->json(['message' => 'Point updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $this->pointRepository->delete($id);
        return response()->json(['message' => 'Point deleted successfully']);
    }

    public function toggleArchive($id): JsonResponse
    {
        $point = $this->pointRepository->toggleArchive($id);
        $message = $point->isArchived() ? 'Point archived successfully' : 'Point unarchived successfully';

        return response()->json([
            'message' => $message,
            'data' => new PointResource($point)
        ]);
    }
}
