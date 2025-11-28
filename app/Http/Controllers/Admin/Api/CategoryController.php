<?php

namespace App\Http\Controllers\Admin\Api;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\SubCategoriesResource;
use App\Http\Resources\CategoriesShowResource;
use App\Repositories\Admin\CategoryRepository;
use App\Http\Resources\SubSubCategoriesResource;
use App\Http\Resources\SubCategoriesShowResource;
use App\Http\Resources\SubSubCategoriesShowResource;
use App\Http\Requests\Admin\Api\Categories\CategoryStoreRequest;
use App\Http\Requests\Admin\Api\Categories\CategoryUpdateRequest;


class CategoryController extends AdminController
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {

        $this->middleware('permission:category_view')->only('index');
        $this->middleware('permission:category_create')->only('store');
        $this->middleware('permission:category_update')->only('update');
        $this->middleware('permission:category_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $level = $request->query('level');
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $categories = $this->categoryRepository->getByLevel($level, $perPage, $search, $categoryId);
        $resource = match ($level) {
            'main' => CategoriesResource::class,
            'sub' => SubCategoriesResource::class,
            'sub-sub' => SubSubCategoriesResource::class,
            default => CategoriesResource::class,
        };

        return response()->json([
            'data' => $resource::collection($categories),
            'pagination' => [
                'currentPage' => $categories->currentPage(),
                'total' => $categories->total(),
                'perPage' => $categories->perPage(),
                'lastPage' => $categories->lastPage(),
                'hasMorePages' => $categories->hasMorePages(),
            ],
        ]);
    }


    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $category = $this->categoryRepository->store($data);
        if ($request->hasFile('image')) {
            $category->addMedia($request->file('image'))
                ->toMediaCollection('category_image');
        }
        return response()->json(['data' => new CategoriesResource($category)]);
    }

    public function show($category, Request $request): JsonResponse
    {
        $level = $request->query('level');
        $category = $this->categoryRepository->findByLevel($level, $category);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        $resource = match ($level) {
            'main' => CategoriesShowResource::class,
            'sub' => SubCategoriesShowResource::class,
            'sub-sub' => SubSubCategoriesShowResource::class,
            default => CategoriesShowResource::class,
        };
        return response()->json(['data' => new $resource($category)]);
    }

    public function update(CategoryUpdateRequest $request, $category): JsonResponse
    {
        $data = $request->validated();

        $categoryModel = $this->categoryRepository->find($category);
        if (!$categoryModel) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category = $this->categoryRepository->update($data, $category);
        if ($request->hasFile('image')) {
            $category->clearMediaCollection('category_image');
            $category->addMedia($request->file('image'))
                ->toMediaCollection('category_image');
        }

        return response()->json(['data' => new CategoriesResource($category)]);
    }

    /**
     * @throws Exception
     */
    public function destroy($category): JsonResponse
    {
        $category = $this->categoryRepository->delete($category);

        if (!$category) {
            return response()->json(['message' => 'Category not found or could not be deleted'], 404);
        }
        return response()->json(['data' => true]);
    }

    public function toggleIsActive($category): JsonResponse
    {
        $category = $this->categoryRepository->toggleIsActive($category);
        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }
        return response()->json(['data' => new CategoriesResource($category)]);
    }
}
