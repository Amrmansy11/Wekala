<?php

namespace App\Http\Controllers\Vendor\API;

use Carbon\Carbon;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\FeedResource;
use App\Http\Resources\FollowResource;
use App\Http\Resources\VendorResource;
use App\Http\Resources\OwnerInfoResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\VendorUserResource;
use App\Repositories\Vendor\FeedRepository;
use Illuminate\Support\Facades\RateLimiter;
use App\Repositories\Vendor\BrandRepository;
use App\Repositories\Vendor\OfferRepository;
use App\Http\Resources\StoreProductsResource;
use App\Repositories\Vendor\VendorRepository;
use App\Repositories\Admin\CategoryRepository;
use App\Repositories\Vendor\VoucherRepository;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Store\StoreBrandResource;
use App\Http\Requests\Vendor\Api\Auth\OTPRequest;
use App\Http\Resources\Store\OffersStoreResource;
use App\Repositories\Vendor\VendorUserRepository;
use App\Http\Resources\Store\FeedStoreInfoResource;
use App\Http\Resources\Store\VouchersStoreResource;
use App\Repositories\Vendor\VoucherCollectRepository;
use Illuminate\Cache\RateLimiter as RateLimiterCache;
use App\Http\Resources\Store\BestSellingStoreResource;
use App\Http\Resources\Consumer\Store\StoreInfoResource;
use App\Http\Resources\Store\NewArrivalProductsResource;
use App\Http\Resources\Store\OffersStoreProductsResource;
use App\Http\Resources\Store\SubSubCategoriesStoreResource;
use App\Http\Resources\Store\TrendingProductsStoreResource;
use App\Http\Requests\Vendor\Api\Store\UpdateOwnerInfoRequest;
use App\Http\Requests\Vendor\Api\Store\StoreUpdateProfileRequest;

class MyStoreController extends VendorController
{
    public function __construct(
        protected VendorRepository         $vendorRepository,
        protected CategoryRepository       $categoryRepository,
        protected FeedRepository           $feedRepository,
        protected VoucherCollectRepository $voucherCollectRepository,
        protected VoucherRepository        $voucherRepository,
        protected BrandRepository          $brandRepository,
        protected OfferRepository          $offerRepository,
        protected VendorUserRepository    $vendorUserRepository,


    ) {
        parent::__construct();
    }

    public function storeInfo(): JsonResponse
    {
        $vendor = $this->vendorRepository->query()
            ->withExists([
                'followers as is_following' => function ($q) {
                    $q->where('follower_id', auth()->user()->vendor_id);
                }
            ])
            ->withCount('followers')
            ->find(AppHelper::getVendorId());

        $feed = $this->feedRepository->query()
            ->where('vendor_id', $vendor->id)
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

    public function getBestSellingProductsByStoreId(): JsonResponse
    {

        $vendor = $this->vendorRepository->find(AppHelper::getVendorId());
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendor->products()
            ->withAvg('reviews', 'rating')
            ->withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->take(3)
            ->get();
        $voucher = $vendor->vouchers()
        ->status('active')
        ->take(5)->get();

        $data = [
            'best_selling' => BestSellingStoreResource::collection($products),
            'vouchers' => VouchersStoreResource::collection($voucher),
        ];
        return response()->json([
            'data' => $data,
            'message' => 'Stores fetched successfully'
        ]);
    }

    public function getTrendingProductsByStoreId(): JsonResponse
    {

        $vendors = $this->vendorRepository->find(AppHelper::getVendorId());
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $products = $vendors->products()
            ->withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->take(5)
            ->get();
        $category = $this->categoryRepository->with(['children.children'])->hasAnyProducts()->find($vendors->category_id);
        $subSubCategories = $category->children
            ->flatMap->children
            ->filter(fn($child) => $child->hasMedia('category_image'));
        $brands = $this->brandRepository->query()
            ->where('vendor_id', $vendors->id)
            ->orWhere('category_id', $vendors->category_id)
            ->has('products')
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

    public function getNewArrivalProductsByStoreId(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $category_id = $request->integer('category_id');
        $vendors = $this->vendorRepository->find(AppHelper::getVendorId());

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



    public function getOffer(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $vendors = $this->vendorRepository->find(AppHelper::getVendorId());
        if (!$vendors) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }
        $offer = $vendors->offers()
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

    public function getOfferProducts(Request $request, int $offer_id): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $category_id = $request->integer('category_id');
        $vendors = $this->vendorRepository->find(AppHelper::getVendorId());
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

    public function getFeedDetails(int $feed_id): JsonResponse
    {

        $feed = $this->feedRepository
            ->query()
            ->where('vendor_id', AppHelper::getVendorId())
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

    public function getAdjacent(Request $request): JsonResponse
    {
        $current_feed_id = $request->integer('current_feed_id');
        $action = $request->string('action', 'next');

        $vendor = $this->vendorRepository->find(AppHelper::getVendorId());
        if (!$vendor) {
            return response()->json([
                'message' => 'No stores found'
            ], 404);
        }

        $feedQuery = $this->feedRepository->query()
            ->with(['products'])
            ->where('vendor_id', $vendor->id)
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



    public function getStoreProductsById(Request $request): JsonResponse
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
        $vendors = $this->vendorRepository->find(AppHelper::getVendorId());
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


    public function getCategoryStoresById(): JsonResponse
    {

        $vendor = $this->vendorRepository->find(AppHelper::getVendorId());

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
    public function requestOTP(OTPRequest $request): JsonResponse
    {
        $maxAttempts = 3;
        $decaySeconds = 60;
        $key = 'otp-request:' . $request->string('action') . '-' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
        RateLimiter::hit($key, $decaySeconds);
        $this->vendorUserRepository->requestOTP([
            'otp_type' => 'phone',
            'otp_value' => $request->string('phone'),
            'action' => $request->string('action'),
        ], auth()->user());
        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }
    public function updateOwnerInfo(UpdateOwnerInfoRequest $request): JsonResponse
    {

        $key = 'update-owner-info|' . request()->ip();
        if ($response = AppHelper::checkRateLimit($key, 3, 5 * 60)) {
            return $response;
        }

        $validator = Validator::make($request->all(), [
            'verification_code' => [
                'required',
                Rule::exists('mobile_otps')
                    ->where('otp_value', $request->string('phone'))
                    ->where('otp_type', 'phone')
                    ->where('action', $request->string('action'))
                    ->where('vendor_user_id', auth()->id())
                    ->where(function ($query) {
                        $query->where(
                            'expires_at',
                            '>',
                            Carbon::now()
                        );
                    })
            ],
        ]);
        if ($validator->fails()) {
            return response()
                ->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        /** @var VendorUser $vendorUser */
        $vendorUser = $this->vendorUserRepository->update([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'phone' => $request->string('phone'),
            'password' => $request->string('password'),
        ], auth()->id());
        app(RateLimiterCache::class)->clear($key);


        return response()->json([
            'data' => new OwnerInfoResource($vendorUser),
            'message' => 'Store updated successfully'
        ]);
    }
    public function updateStoreInfo(StoreUpdateProfileRequest $request): JsonResponse
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


    public function followers(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $type = $request->get('type', 'following'); // default => following

        /** @var Vendor $vendor */
        $vendor = $this->vendorRepository->find(AppHelper::getVendorId());

        $relation = $type === 'followers' ? 'followers' : 'following';

        $follows = $vendor->$relation()->paginate($perPage);

        return response()->json([
            'data' => FollowResource::collection($follows),
            'pagination' => [
                'currentPage'   => $follows->currentPage(),
                'total'         => $follows->total(),
                'perPage'       => $follows->perPage(),
                'lastPage'      => $follows->lastPage(),
                'hasMorePages'  => $follows->hasMorePages(),
            ]
        ]);
    }
}
