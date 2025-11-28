<?php

namespace App\Http\Controllers\Consumer\API;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Vendor\WishlistRepository;
use App\Http\Resources\Consumer\Wishlist\WishlistResource;
use App\Http\Requests\Vendor\Api\Wishlist\WishlistStoreRequest;

class WishlistController extends ConsumerController
{
    public function __construct(protected WishlistRepository $wishlistRepository)
    {
        // $this->middleware('permission:vendor_sizes_templates_view')->only('index');
        // $this->middleware('permission:vendor_sizes_templates_create')->only('store');
        // $this->middleware('permission:vendor_sizes_templates_update')->only('update');
        // $this->middleware('permission:vendor_sizes_templates_delete')->only('destroy');
        parent::__construct();
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 15);
        $consumerUser = Auth::guard('consumer-api')->user();

        $wishlist = $consumerUser->wishlist()
            ->with('product')
            ->paginate($perPage);

        return response()->json([
            'data' => WishlistResource::collection($wishlist),
            'pagination' => [
                'currentPage' => $wishlist->currentPage(),
                'total' => $wishlist->total(),
                'perPage' => $wishlist->perPage(),
                'lastPage' => $wishlist->lastPage(),
                'hasMorePages' => $wishlist->hasMorePages(),
            ]
        ]);
    }



    public function store(WishlistStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::guard('consumer-api')->user();
        $result = $this->wishlistRepository->toggleWishlist($user, $data['product_id']);
        return response()->json($result);
    }
}
