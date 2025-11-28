<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TagResource;
use App\Http\Resources\TagShowResource;
use App\Repositories\Admin\TagRepository;
use App\Http\Requests\Admin\Api\Tags\TagStoreRequest;
use App\Http\Requests\Admin\Api\Tags\TagUpdateRequest;


class TagController extends AdminController
{
    public function __construct(protected TagRepository $tagRepository)
    {
        $this->middleware('permission:tags_view')->only('index');
        $this->middleware('permission:tags_create')->only('store');
        $this->middleware('permission:tags_update')->only('update');
        $this->middleware('permission:tags_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $search = $request->string('search');
        $categoryId = $request->integer('category_id');
        $tags = $this->tagRepository->query()->when(
            $search,
            fn($query) =>
            $query->where('name', 'like', "%{$search}%")
        )->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->paginate($perPage);
        return response()->json([
            'data' => TagResource::collection($tags),
            'pagination' => [
                'currentPage' => $tags->currentPage(),
                'total' => $tags->total(),
                'perPage' => $tags->perPage(),
                'lastPage' => $tags->lastPage(),
                'hasMorePages' => $tags->hasMorePages(),
            ]
        ]);
    }

    public function store(TagStoreRequest $request): JsonResponse
    {
        $tag = $this->tagRepository->store($request->validated());
        return response()->json(['data' => new TagResource($tag)]);
    }

    public function show($tag): JsonResponse
    {
        $tag = $this->tagRepository->find($tag);
        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }
        return response()->json(['data' => new TagShowResource($tag)]);
    }

    public function update(TagUpdateRequest $request, $tag): JsonResponse
    {
        $tag = $this->tagRepository->update($request->validated(), $tag);
        return response()->json(['data' => new TagResource($tag)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($tag): JsonResponse
    {
        $tag = $this->tagRepository->delete($tag);
        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }
        return response()->json(['data' => true]);
    }
    //toggleIsActive
    public function toggleIsActive($tag): JsonResponse
    {
        $tag = $this->tagRepository->toggleIsActive($tag);
        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }
        return response()->json(['data' => new TagResource($tag)]);
    }
}
