<?php

namespace App\Http\Controllers\Vendor\API;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FeedResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\VendorResource;
use App\Repositories\Vendor\FeedRepository;
use App\Repositories\Vendor\BrandRepository;
use App\Repositories\Vendor\OfferRepository;
use App\Http\Resources\StoreProductsResource;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Admin\CategoryRepository;
use App\Repositories\Vendor\VoucherRepository;
use App\Http\Resources\Store\StoreBrandResource;
use App\Http\Resources\Store\OffersStoreResource;
use App\Http\Resources\Store\FeedStoreInfoResource;
use App\Http\Resources\Store\VouchersStoreResource;
use App\Repositories\Vendor\VoucherCollectRepository;
use App\Http\Resources\Store\BestSellingStoreResource;
use App\Http\Resources\Consumer\Store\StoreInfoResource;
use App\Http\Resources\Store\NewArrivalProductsResource;
use App\Http\Resources\Store\OffersStoreProductsResource;
use App\Http\Resources\Store\SubSubCategoriesStoreResource;
use App\Http\Resources\Store\TrendingProductsStoreResource;
use App\Http\Requests\Vendor\Api\Store\StoreUpdateImageRequest;
use App\Http\Requests\Vendor\Api\Voucher\VoucherCollectRequest;
use App\Http\Requests\Vendor\Api\Store\StoreUpdateProfileRequest;

class StoreController extends VendorController
{
    public function __construct(
        protected VendorRepository         $vendorRepository,
        protected CategoryRepository       $categoryRepository,
        protected FeedRepository           $feedRepository,
        protected VoucherCollectRepository $voucherCollectRepository,
        protected VoucherRepository        $voucherRepository,
        protected BrandRepository          $brandRepository,
        protected OfferRepository          $offerRepository


    ) {
        parent::__construct();
    }

    public function storeInfo(int $id): JsonResponse
    {
        $vendor = $this->vendorRepository->query()
            ->withExists([
                'followers as is_following' => function ($q) {
                    $q->where('follower_id', auth()->user()->vendor_id);
                }
            ])
            ->withCount('followers')
            ->find($id);
        $feed = $this->feedRepository->query()
            ->where('vendor_id', $id)
            ->where('type', 'story')
            ->whereDate('created_at', now()->toDateString())
            ->latest()
            ->get();
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $data = [
            'store-info' => new StoreInfoResource($vendor),
            'feeds' => FeedStoreInfoResource::collection($feed),
        ];
        return response()->json([
            'data' => $data,
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getBestSellingProductsByStoreId(int $id): JsonResponse
    {

        $vendors = $this->vendorRepository->find($id);
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendors->products()->withAvg('reviews', 'rating')->inRandomOrder()->take(3)->get();
        $voucher = $vendors->vouchers()->where('start_date', '<', now())->where('end_date', '>', now())->take(5)->get();

        $data = [
            'best_selling' => BestSellingStoreResource::collection($products),
            'vouchers' => VouchersStoreResource::collection($voucher),
        ];
        return response()->json([
            'data' => $data,
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getTrendingProductsByStoreId(int $id): JsonResponse
    {

        $vendors = $this->vendorRepository->find($id);
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendors->products()->inRandomOrder()->take(5)->latest()->get();
        $category = $this->categoryRepository->with(['children.children'])->find($vendors->category_id);
        $subSubCategories = $category->children
            ->flatMap->children
            ->filter(fn($child) => $child->hasMedia('category_image'));
        $brands = $this->brandRepository->query()
            ->where('vendor_id', $id)
            ->orWhere('category_id', $vendors->category_id)
            ->get();


        $data = [
            'trending' => TrendingProductsStoreResource::collection($products),
            'categories' => SubSubCategoriesStoreResource::collection($subSubCategories),
            'brands' => StoreBrandResource::collection($brands),
        ];
        return response()->json([
            'data' => $data,
        ]);
    }

    public function getNewArrivalProductsByStoreId(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $category_id = $request->integer('category_id');
        $vendors = $this->vendorRepository->find($id);

        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendors->products()
            ->when($category_id, function ($query) use ($category_id) {
                $query->where('category_id', $category_id);
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->latest('created_at')
            ->paginate($perPage);
        return response()->json([
            'data' => NewArrivalProductsResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function collectVoucher(VoucherCollectRequest $request, int $id): JsonResponse
    {
        if ($this->voucherCollectRepository
            ->query()->where('vendor_id', $id)
            ->where('voucher_id', $request->voucher_id)
            ->exists()
        ) {
            return response()->json([
                'message' => 'You have already collected this voucher'
            ], 400);
        }
        $voucher = $this->voucherRepository->find($request->voucher_id);
        $collectedCount = $this->voucherCollectRepository->query()
            ->where('voucher_id', $request->voucher_id)
            ->count();
        if ($collectedCount >= $voucher->number_of_use) {
            return response()->json([
                'message' => 'This voucher has reached the maximum number of uses'
            ], 400);
        }
        $data = $request->validated();
        $data['vendor_id'] = $id;
        $this->voucherCollectRepository->store($data);
        return response()->json([
            'data' => null,
            'message' => 'Voucher collected successfully'
        ]);
    }

    public function getOffer(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $vendors = $this->vendorRepository->find($id);
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $offer = $vendors->offers()->where('start', '<', now())->where('end', '>', now())->paginate($perPage);
        return response()->json([
            'data' => OffersStoreResource::collection($offer),
            'pagination' => [
                'currentPage' => $offer->currentPage(),
                'total' => $offer->total(),
                'perPage' => $offer->perPage(),
                'lastPage' => $offer->lastPage(),
                'hasMorePages' => $offer->hasMorePages(),
            ],
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getOfferProducts(Request $request, int $id, int $offer_id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $category_id = $request->integer('category_id');
        $vendors = $this->vendorRepository->find($id);
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $offer = $vendors->offers()
            ->with(['products' => function ($query) use ($category_id) {
                $query->when($category_id, function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            }])
            ->where('end', '>', now())->paginate($perPage)
            ->find($offer_id);


        return response()->json([
            'data' => new OffersStoreProductsResource($offer),
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getFeedDetails(int $id, int $feed_id): JsonResponse
    {

        $feed = $this->feedRepository
            ->query()
            ->where('vendor_id', $id)
            ->with(['products'])
            ->where('type', 'feed')
            ->find($feed_id);
        if (!$feed) {
            return response()->json([
                'message' => 'No feeds found'
            ], 404);
        }
        return response()->json([
            'data' => new FeedResource($feed),
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getAdjacent(Request $request, int $id): JsonResponse
    {
        $current_feed_id = $request->integer('current_feed_id');
        $action = $request->string('action', 'next');

        $vendor = $this->vendorRepository->find($id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }

        $feedQuery = $this->feedRepository->query()
            ->with(['products'])
            ->where('vendor_id', $id)
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



    public function getStoreProductsById(Request $request, int $id): JsonResponse
    {

        $perPage = $request->integer('per_page', 15);
        $filters = [
            'search' => $request->string('search'),
            'category_id' => $request->array('category_id', []),
            'size_id' => $request->array('size_id', []),
            'color_id' => $request->array('color_id', []),
            'material_id' => $request->array('material_id', []),
            'tag_id' => $request->array('tag_id', []),
            'brand_id' => $request->array('brand_id', [])
        ];
        $vendors = $this->vendorRepository->find($id);
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendors->products()->filter($filters)->paginate($perPage);
        return response()->json([
            'data' => StoreProductsResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ]
        ]);
    }


    public function getCategoryStoresById(int $id): JsonResponse
    {

        $vendor = $this->vendorRepository->find($id);

        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $category = $this->categoryRepository
            ->with(['children.children'])
            ->hasAnyProducts()
            ->find($vendor->category_id);

        $subSubCategories = $category->children
            ->flatMap->children
            ->filter(fn($child) => $child->hasMedia('category_image'));

        return response()->json([
            'data' => SubSubCategoriesStoreResource::collection($subSubCategories),
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function updateStoreProfile(StoreUpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $vendor_id = AppHelper::getVendorId();
        $vendor = $this->vendorRepository->find($vendor_id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $vendor->update($data);
        return response()->json([
            'data' => new VendorResource($vendor),
            'message' => 'Store updated successfully'
        ]);
    }
    public function updateStoreImages(StoreUpdateImageRequest $request): JsonResponse
    {
        $vendor_id = AppHelper::getVendorId();
        $vendor = $this->vendorRepository->find($vendor_id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        if ($request->hasFile('logo')) {
            $vendor->clearMediaCollection('vendor_logo');
            $vendor->addMedia($request->file('logo'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_logo');
        }
        if ($request->hasFile('cover')) {
            $vendor->clearMediaCollection('vendor_cover');
            $vendor->addMedia($request->file('cover'))
                ->usingName($vendor->store_name)
                ->toMediaCollection('vendor_cover');
        }
        return response()->json([
            'data' => new VendorResource($vendor),
            'message' => 'Store updated successfully'
        ]);
    }
}
