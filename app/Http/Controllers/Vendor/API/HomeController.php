<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FeedResource;
use App\Http\Resources\Home\BrandResource;
use App\Http\Resources\HomeSliderResource;
use App\Http\Resources\Home\SliderResource;
use App\Repositories\Vendor\FeedRepository;
use App\Http\Resources\Home\ProductResource;
use App\Repositories\Admin\SliderRepository;
use App\Repositories\Vendor\BrandRepository;
use App\Http\Resources\Home\FlashSaleResource;
use App\Repositories\Vendor\ProductRepository;
use App\Repositories\Admin\FlashSaleRepository;
use App\Http\Resources\Home\ElWekalaCollectionsResource;
use App\Repositories\Admin\ElwekalaCollectionRepository;
use App\Http\Resources\Home\ElWekalaCollectionsHomeResource;

class HomeController extends VendorController
{

    public function __construct(
        protected ElwekalaCollectionRepository $elwekalaCollectionRepository,
        protected SliderRepository             $sliderRepository,
        protected FlashSaleRepository          $flashSaleRepository,
        protected BrandRepository              $brandRepository,
        protected ProductRepository            $productRepository,
        protected FeedRepository           $feedRepository,


    ) {}

    public function index(): JsonResponse
    {
        $flashSales = $this->flashSaleRepository->query()
            ->where('type', 'flash_sale')
            ->with('product')
            ->has('product')
            ->take(10)
            ->get();
        $collections = $this->elwekalaCollectionRepository
            ->query()
            ->whereNot('type', 'flash_sale')
            ->with(['product' => function ($query) {
                $query->select('id', 'name');
            }])
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
            ->where('vendor_id', auth()->check() ? AppHelper::getVendorId() : null)->orWhereNull('vendor_id')
            ->where('is_active', true)
            ->withCount('products')
            ->orderByDesc('products_count')
            ->take(10)
            ->get();

        $sliders = $this->sliderRepository->query()->take(6)->get();

        return response()->json([
            'flash_sales' => FlashSaleResource::collection($flashSales),
            'collections' => $collectionsData,
            'brands' => BrandResource::collection($brands),
            'sliders' => HomeSliderResource::collection($sliders),
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
            ->whereHas('product', function ($q) use ($filters) {
                $q->filter($filters);
            })
            ->with(['product.vendor'])
            ->has('product');

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
        //        }

        //        $data = $query->get()->groupBy(fn($item) => $item->product->vendor->id);
        //        return response()->json(VendorWithProductsResource::collection($data));
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
    public function getFeeds(): JsonResponse
    {
        $feed = $this->feedRepository->query()
            ->where('type', 'feed')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();
        return response()->json([
            'data' => FeedResource::collection($feed)
        ]);
    }
    public function getAdjacent(Request $request): JsonResponse
    {
        $current_feed_id = $request->integer('current_feed_id');
        $action = $request->string('action', 'next');



        $feedQuery = $this->feedRepository->query()
            ->with(['products'])
            ->where('type', 'feed')
            ->when($current_feed_id, function ($query) use ($current_feed_id, $action) {
                if ($action == 'next') {
                    $query->where('id', '>', $current_feed_id)
                        ->orderBy('id', 'asc');
                } else {
                    $query->where('id', '<', $current_feed_id)
                        ->orderBy('id', 'desc');
                }
            }, function ($query) {
                $query->orderBy('id', 'desc');
            });

        $feed = $feedQuery->first();

        return response()->json([
            'data' => $feed ? new FeedResource($feed) : null,
            'message' => $feed ? 'Feed fetched successfully' : 'No feeds available'
        ]);
    }


    public function getTopBrands(): JsonResponse
    {
        $brands = $this->brandRepository->query()
            ->where('vendor_id', auth()->check() ? AppHelper::getVendorId() : null)
            ->orWhereNull('vendor_id')
            ->where('is_active', true)
            ->get();
        return response()->json(['data' => BrandResource::collection($brands)]);
    }

    public function getJustForYou(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $products = $this->productRepository->query()
            ->inRandomOrder()
            ->with('variants')
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

    public function getSliders(): JsonResponse
    {
        $sliders = $this->sliderRepository->query()->get();
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
            ->first();

        if (!$slider) {
            return response()->json(['message' => 'Slider not found'], 404);
        }
        $products = $slider->products()
            ->with(['variants'])
            ->filter($filters)
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

    public function getRecentProducts(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);

        // Get products created in the last 15 days, ordered by latest first
        $products = $this->productRepository->query()
            ->where('created_at', '>=', now()->subDays(15))
            ->with(['variants', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
        ]);
    }
}
