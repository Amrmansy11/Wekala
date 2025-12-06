<?php

namespace App\Http\Controllers\Consumer\API;

use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Repositories\Vendor\FeedRepository;
use App\Repositories\Vendor\BrandRepository;
use App\Repositories\Vendor\OfferRepository;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Admin\CategoryRepository;
use App\Repositories\Vendor\ProductRepository;
use App\Repositories\Vendor\VoucherRepository;
use App\Http\Resources\Consumer\Store\FeedResource;
use App\Repositories\Vendor\VoucherCollectRepository;
use App\Http\Resources\Consumer\Store\StoreInfoResource;
use App\Http\Controllers\Consumer\API\ConsumerController;
use App\Http\Resources\Consumer\Store\StoreBrandResource;
use App\Http\Resources\Consumer\Gifts\GiftListingResource;
use App\Http\Resources\Consumer\Store\OffersStoreResource;
use App\Http\Resources\Consumer\Store\FeedStoreInfoResource;
use App\Http\Resources\Consumer\Store\StoreProductsResource;
use App\Http\Resources\Consumer\Store\VouchersStoreResource;
use App\Http\Resources\Consumer\Store\BestSellingStoreResource;
use App\Http\Resources\Consumer\Store\NewArrivalProductsResource;
use App\Http\Resources\Consumer\Point\PointProductPreviewResource;
use App\Http\Resources\Consumer\Store\OffersStoreProductsResource;
use App\Http\Resources\Consumer\Store\SubSubCategoriesStoreResource;
use App\Http\Resources\Consumer\Store\TrendingProductsStoreResource;
use App\Models\VendorFollow;

class StoreController extends ConsumerController
{
    public function __construct(
        protected VendorRepository         $vendorRepository,
        protected CategoryRepository       $categoryRepository,
        protected FeedRepository           $feedRepository,
        protected VoucherCollectRepository $voucherCollectRepository,
        protected VoucherRepository        $voucherRepository,
        protected BrandRepository          $brandRepository,
        protected OfferRepository          $offerRepository,
        protected ProductRepository $productRepository,



    ) {
        parent::__construct();
    }

    public function storeInfo(int $id): JsonResponse
    {
        $vendor = $this->vendorRepository->query()
            ->withExists([
                'followers as is_following' => function ($q) {
                    $q->where('follower_id', auth()->user()->id);
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

        $vendor = $this->vendorRepository->find($id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendor->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->orderByDesc('sold_count')
            ->B2BB2C()
            ->take(3)
            ->get();
        $voucher = $vendor
            ->vouchers()
            ->status('active')
            ->take(5)
            ->get();

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
        $products = $vendors->products()
            ->with(['variants', 'reviews'])
            ->withSum('orderItems as sold_count', 'quantity')
            ->orderByDesc('sold_count')
            ->B2BB2C()
            ->take(3)
            ->get();
        $category = $this->categoryRepository
            ->with(['children.children'])
            ->find($vendors->category_id);
        $subSubCategories = $category
            ->children
            ->flatMap->children
            ->filter(fn($child) => $child->hasMedia('category_image'));
        $brands = $this->brandRepository
            ->query()
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
            ->B2BB2C()
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
    public function getStorePointsById(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $type = $request->string('type', 'earned');
        $vendor = $this->vendorRepository->find($id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }


        $type = $request->string('type', 'earned');
        $perPage = $request->integer('per_page', 15);
        $products = $this->productRepository->query()
            ->withWhereHas('points', function ($q) use ($type, $id) {
                $q->where('type', $type)->active()->where('vendor_id', $id);
            })
            ->B2BB2C()
            ->paginate($perPage);
        return response()->json([
            'data' => PointProductPreviewResource::collection($products),
            'pagination' => [
                'currentPage' => $products->currentPage(),
                'total' => $products->total(),
                'perPage' => $products->perPage(),
                'lastPage' => $products->lastPage(),
                'hasMorePages' => $products->hasMorePages(),
            ],
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
        $offer = $vendors
            ->offers()
            ->status('active')
            ->paginate($perPage);
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
            ->wherehas('products', function ($q) {
                $q->B2BB2C();
            })
            ->status('active')
            ->find($offer_id);
        if (!$offer) {
            return response()->json([
                'message' => 'No offers found'
            ], 404);
        }

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
            ->whereHas('products', function ($q) {
                $q->B2BB2C();
            })
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
            ->whereHas('products', function ($q) {
                $q->B2BB2C();
            })
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
        $products = $vendors->products()
            ->with(['points' => function ($query) {
                $query->whereNull('archived_at');
            }, 'discounts' => function ($query) {
                $query->whereNull('archived_at');
            }])
            ->filter($filters)
            ->B2BB2C()
            ->paginate($perPage);
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
            ->find($vendor->category_id);

        $subSubCategories = $category->children
            ->flatMap->children
            ->filter(fn($child) => $child->hasMedia('category_image'));

        return response()->json([
            'data' => SubSubCategoriesStoreResource::collection($subSubCategories),
            'message' => 'Stores fetched successfully'
        ]);
    }
    public function getVoucherProducts(Request $request, int $id, int $voucher_id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $vendor = $this->vendorRepository->find($id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $voucher = $vendor->vouchers()
            ->where('id', $voucher_id)
            ->status('active')
            ->first();
        if (!$voucher) {
            return response()->json([
                'message' => 'No vouchers found'
            ], 404);
        }
        $products = $voucher
            ->products()
            ->B2BB2C()
            ->paginate($perPage);

        return response()->json([
            'data' => StoreProductsResource::collection($products),
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
    public function getGiftsByStoreId(Request $request, int $id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $vendor = $this->vendorRepository->find($id);
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $gifts = $vendor->gifts()
            ->with(['sourceProduct', 'giftProduct'])
            ->whereHas('sourceProduct', function ($q) {
                $q->B2BB2C();
            })
            ->whereHas('giftProduct', function ($q) {
                $q->B2BB2C();
            })
            ->active()
            ->paginate($perPage);

        return response()->json([
            'data' => GiftListingResource::collection($gifts),
            'pagination' => [
                'currentPage' => $gifts->currentPage(),
                'total' => $gifts->total(),
                'perPage' => $gifts->perPage(),
                'lastPage' => $gifts->lastPage(),
                'hasMorePages' => $gifts->hasMorePages(),
            ]
        ]);
    }

    public function toggleFollow(int $id): JsonResponse
    {
        $userId = auth('consumer-api')->id();
        $store = $this->vendorRepository->find($id);

        if (!$store) {
            return response()->json([
                'message' => 'Store not found',
            ], 404);
        }

        $existingFollow = VendorFollow::where('vendor_id', $id)
            ->where('follower_id', $userId)
            ->first();

        if ($existingFollow) {
            $existingFollow->delete();
            return response()->json([
                'data' => null,
                'message' => 'Unfollowed successfully',
            ]);
        }

        VendorFollow::create([
            'vendor_id' => $id,
            'follower_id' => $userId,
        ]);

        return response()->json([
            'data' => null,
            'message' => 'Followed successfully',
        ]);
    }
}
