<?php

namespace App\Http\Controllers\Vendor\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Admin\CategoryRepository;
use App\Http\Resources\CategoriesVendorResource;
use App\Http\Controllers\Vendor\API\VendorController;


class CategoryController extends VendorController
{
    public function __construct(protected CategoryRepository $categoryRepository)
    {

        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $parentId = $request->integer('parent_id', null);
        $search = $request->query('search');
        $categories = $this->categoryRepository->query()
            ->when(
                $parentId,
                fn($query) => $query->where('parent_id', $parentId),
                fn($query) => $query->whereNull('parent_id')
            )
            ->when(
                $search,
                fn($query) =>
                $query->whereAny(['name->ar', 'name->en'], 'like', "%{$search}%")
            )
            ->hasAnyProducts()
            ->where('is_active', true)
            ->paginate($perPage);


        return response()->json([
            'data' => CategoriesVendorResource::collection($categories),
            'pagination' => [
                'currentPage' => $categories->currentPage(),
                'total' => $categories->total(),
                'perPage' => $categories->perPage(),
                'lastPage' => $categories->lastPage(),
                'hasMorePages' => $categories->hasMorePages(),
            ]
        ]);
    }
}
