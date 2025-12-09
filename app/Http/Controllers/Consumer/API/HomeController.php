<?php

namespace App\Http\Controllers\Consumer\API;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FeedResource;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\Home\BrandResource;
use App\Http\Resources\HomeSliderResource;
use App\Repositories\Vendor\FeedRepository;
use App\Repositories\Admin\SliderRepository;
use App\Repositories\Vendor\BrandRepository;
use App\Http\Resources\Home\FlashSaleResource;
use App\Repositories\Admin\CategoryRepository;
use App\Repositories\Vendor\ProductRepository;
use App\Repositories\Admin\FlashSaleRepository;
use App\Http\Resources\Consumer\Home\SliderResource;
use App\Http\Resources\Consumer\Home\ProductResource;
use App\Repositories\Admin\ElwekalaCollectionRepository;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Home\JustForYouProductResource;
use App\Http\Resources\Consumer\Home\ElWekalaCollectionsResource;
use App\Http\Resources\Consumer\Point\PointProductPreviewResource;
use App\Http\Resources\Consumer\Home\ElWekalaCollectionsHomeResource;

class HomeController extends ConsumerController
{

    public function __construct(
        protected ElwekalaCollectionRepository $elwekalaCollectionRepository,
        protected SliderRepository             $sliderRepository,
        protected FlashSaleRepository          $flashSaleRepository,
        protected BrandRepository              $brandRepository,
        protected ProductRepository            $productRepository,
        protected FeedRepository           $feedRepository,
        protected CategoryRepository           $categoryRepository


    ) {}

    public function index(): JsonResponse
    {
        $flashSales = $this->flashSaleRepository->query()
            ->where('type', 'flash_sale')
            ->where('type_elwekala', 'consumer')
            ->withWhereHas('product', function ($q) {
                $q->B2BB2C();
            })
            ->take(10)
            ->get();
        $collections = $this->elwekalaCollectionRepository
            ->query()
            ->whereNot('type', 'flash_sale')
            ->where('type_elwekala', 'consumer')
            ->withWhereHas('product', function ($query) {
                $query->select('id', 'name')->B2BB2C();
            })
            ->get()
            ->groupBy('type');

        $collectionsData = $collections->map(function ($items, $type) {
            return [
                'type' => $type,
                'products' => $items->take(1)->map(function ($item) {
                    return new ElWekalaCollectionsResource($item);
                }),
            ];
        })->values();

        $brands = $this->brandRepository->query()
            ->where('is_active', true)
            ->has('products')
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(10)
            ->get();



        $sliders = $this->sliderRepository->query()->where('type', 'consumer')->take(6)->get();
        $categories = $this->categoryRepository->query()->whereHas('parent', fn($q) => $q->whereNotNull('parent_id'))->get();

        return response()->json([
            'flash_sales' => FlashSaleResource::collection($flashSales),
            'collections' => $collectionsData,
            'brands' => BrandResource::collection($brands),
            'sliders' => HomeSliderResource::collection($sliders),
            'categories' => CategoriesResource::collection($categories),
        ]);
    }

    public function getByCategory($slug, Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $types = [
            'flash-sale' => 'flash_sale',
            'feeds' => 'feeds',
            'best-sellers' => 'best_sellers',
            'new-arrivals' => 'new_arrivals',
            'most-popular' => 'most_popular'
        ];
        $filters = [
            'search'      => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id'     => $request->array('size_id', []),
            'color_id'    => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id'        => $request->array('tag_id', [])
        ];


        if (!array_key_exists($slug, $types)) {
            return response()->json();
        }
        $query = $this->elwekalaCollectionRepository
            ->query()
            ->where('type', $types[$slug])
            ->where('type_elwekala', 'consumer')
            ->withWhereHas('product', function ($q) use ($filters) {
                $q->B2BB2C()
                    ->with('variants')
                    ->filter($filters);
            });

        //        if ($slug === 'flash-sale') {
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => ElWekalaCollectionsHomeResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
        ]);
    }


    public function getJustForYou(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $products = $this->productRepository->query()
            ->inRandomOrder()
            ->B2BB2C()
            ->with(['variants','orderItems','discounts','points','offer','reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->paginate($perPage);
        return response()->json([
            'data' => JustForYouProductResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }

    public function getSliders(): JsonResponse
    {
        $sliders = $this->sliderRepository->query()
            ->where('type', 'consumer')
            ->get();
        return response()->json([
            'data' => HomeSliderResource::collection($sliders)
        ]);
    }

    public function getSliderDetails($id, Request $request): JsonResponse
    {

        $perPage = $request->integer('per_page', 15);
        $filters = [
            'search'      => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id'     => $request->array('size_id', []),
            'color_id'    => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id'      => $request->array('tag_id', [])

        ];
        $slider = $this->sliderRepository->query()
            ->where('id', $id)
            ->where('type', 'consumer')
            ->first();

        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }
        $products = $slider->products()
            ->with(['variants'])
            ->filter($filters)
            ->B2BB2C()
            ->paginate($perPage);

        return response()->json([
            'data' => SliderResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
        ]);
    }
    public function getBrandProductsById(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $filters = [
            'search'      => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id'     => $request->array('size_id', []),
            'color_id'    => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id'      => $request->array('tag_id', [])

        ];
        $products = $this->brandRepository->find($id)
            ->products()
            ->B2BB2C()
            ->with(['variants'])
            ->filter($filters)
            ->paginate($perPage);
        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }

      public function getSubSubCategoryProductsById(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $products = $this->productRepository->query()
            ->where('sub_sub_category_id', $id)
            ->B2BB2C()
            ->with(['variants'])
            ->paginate($perPage);
        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }
}
