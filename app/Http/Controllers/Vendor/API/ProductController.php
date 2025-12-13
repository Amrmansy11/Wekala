<?php

namespace App\Http\Controllers\Vendor\API;

use App\Models\Order;
use App\Helpers\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductDetailsResource;
use App\Repositories\Vendor\ProductRepository;
use App\Http\Requests\Vendor\Api\Product\ProductStoreRequest;
use App\Models\Scopes\ActiveProduct;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        // $this->middleware('permission:vendor_products_view')->only('index');
        // $this->middleware('permission:vendor_products_create')->only('store');
        // $this->middleware('permission:vendor_products_update')->only('update');
        // $this->middleware('permission:vendor_products_delete')->only('destroy');
        $this->productRepository = $productRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $status = $request->get('status');

        $query = $this->productRepository->query()
            ->withoutGlobalScopes()
            ->where('vendor_id', AppHelper::getVendorId())
            // ->sellersOnly()
            ->with('category', 'brand', 'tags', 'sizes', 'variants', 'reviews')
            ->withCount('wishlists as favorites_count');

        // Apply status filter if provided
        if ($status) {
            if ($status === 'soon') {
                $query->where('published_at', '>', now());
            } elseif (in_array($status, \App\Enums\ProductStatus::toArray(), true)) {
                $query->where('status', $status);
            }
        }

        $products = $query->paginate($perPage);
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

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['vendor_id'] = AppHelper::getVendorId();
        $product = $this->productRepository->store($data);
        return response()->json([
            'message' => 'Product created successfully',
            'product' => new ProductDetailsResource($product)
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $product = $this->productRepository->show($id);
        return response()->json(['data' => new ProductDetailsResource($product)]);
    }

    // public function sales($id): JsonResponse
    // {
    //     $product = $this->productRepository->show($id);

    //     if (!$product) {
    //         return response()->json(['message' => 'Product not found'], 404);
    //     }

    //     $type = request()->get('type'); // b2b or b2c or null
    //     $buyerVendorId = request()->get('buyer_vendor_id'); // optional for B2B filter

    //     $sales = CartItem::query()
    //         ->where('product_id', $product->id)
    //         ->with([
    //             'cart.user',          // لو B2C
    //             'cart.vendor',        // لو B2B
    //             'vendorUser',         // موظف الفندور اللي عمل الطلب
    //             'product',
    //             'variant'
    //         ]);

    //     // ----------- فلتر B2C فقط -----------
    //     if ($type === 'b2c') {
    //         $sales->whereHas('cart', function ($q) {
    //             $q->whereNotNull('user_id')
    //               ->where('status', '!=', 'open'); // يعني فعلاً اتشترى
    //         });
    //     }

    //     // ----------- فلتر B2B فقط -----------
    //     if ($type === 'b2b') {
    //         $sales->whereHas('cart', function ($q) {
    //             $q->whereNotNull('vendor_id')
    //               ->where('status', '!=', 'open');
    //         });

    //         // فلتر vendor معيّن جوا B2B
    //         if (request()->filled('buyer_vendor_id')) {
    //             $sales->whereHas('cart', function ($q) use ($buyerVendorId) {
    //                 $q->where('vendor_id', $buyerVendorId);
    //             });
    //         }
    //     }

    //     // لو مفيش type → رجّع الاتنين
    //     if (!$type) {
    //         $sales->whereHas('cart', fn($q) => $q->where('status', '!=', 'open'));
    //     }

    //     $sales = $sales->get();

    //     return response()->json([
    //         'product' => $product,
    //         'sales'   => $sales
    //     ]);
    // }

    public function sales($productId): JsonResponse
    {
        $type = request()->get('type', 'b2c'); // b2b أو b2c
        $buyerVendorId = request()->get('buyer_vendor_id'); // optional filter

        $orders = Order::query()
            ->whereHas('items', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->with(['items' => function ($q) use ($productId) {
                $q->where('product_id', $productId);
            }]);

        if ($type === 'b2c') {
            $orders->whereNotNull('user_id');
        }

        if ($type === 'b2b') {
            $orders->whereNotNull('buyer_vendor_id');
            if ($buyerVendorId) {
                $orders->where('buyer_vendor_id', $buyerVendorId);
            }
        }

        $orders = $orders->get();

        // Transform to structured array for UI
        $sales = $orders->map(function ($order) use ($type) {
            if ($type === 'b2c') {
                return $order->items->map(function ($item) use ($order) {
                    return [
                        'name' => $order->user->name ?? 'Unknown',
                        'image' => $order->user->image ?? null,
                        'items_count' => $item->quantity,
                        'rating' => $order->user->rating ?? null,
                    ];
                });
            } else { // B2B
                return $order->items->map(function ($item) use ($order) {
                    return [
                        'vendor_name' => $order->buyerVendor->name ?? 'Unknown',
                        'vendor_logo' => $order->buyerVendor->logo ?? null,
                        'items_count' => $item->quantity,
                    ];
                });
            }
        })->flatten(1);

        return response()->json([
            'sales' => $sales,
        ]);
    }
}
