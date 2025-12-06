<?php

namespace App\Http\Controllers\Vendor\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\Api\Cart\AddToCartRequest;
use App\Http\Requests\Vendor\Api\Cart\ShippingAddressRequest;
use App\Http\Resources\Consumer\Cart\CartShippingAddressesResource;
use App\Models\Cart;
use App\Models\CartShippingAddress;
use App\Models\Vendor;
use App\Models\VendorUser;
use App\Repositories\Vendor\CartRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly CartRepository $cartRepository)
    {
    }

    public function index(): JsonResponse
    {
        $grouped = $this->cartRepository->getMyCartGroupedByVendor();
        return response()->json(['data' => $grouped]);
    }

    /**
     * @throws Exception
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartRepository->addItem($request->validated());
        return response()->json(['data' => $cart->load('items')]);
    }

    public function shippingAddresses(): JsonResource
    {
        $vendor_id = Auth::guard('vendor-api')->user()->vendor_id;
        $vendorUsers = VendorUser::query()->where('vendor_id', $vendor_id)->pluck('id')->toArray();
        $addresses = CartShippingAddress::query()->whereIn('addressable_id', $vendorUsers)
            ->where('addressable_type', VendorUser::class)
            ->get();
        return CartShippingAddressesResource::collection($addresses);

    }

    public function shippingAddress(ShippingAddressRequest $request): JsonResponse
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        $shippingAddress = CartShippingAddress::query()->updateOrCreate([
            'addressable_type' => VendorUser::class,
            'addressable_id' => $vendorUser->id,
            'address_type' => $request->input('address_type'),
        ], [
            'address_type' => $request->input('address_type'),
            'recipient_name' => $request->input('recipient_name'),
            'recipient_phone' => $request->input('recipient_phone'),
            'full_address' => $request->input('full_address'),
            'state_id' => $request->input('state_id'),
            'city_id' => $request->input('city_id'),
        ]);
        return response()->json(['data' => $shippingAddress]);
    }

    /**
     * @throws ValidationException
     */
    public function destroy(int $id): JsonResponse
    {
        $this->cartRepository->removeItem($id);
        return response()->json(['message' => 'Item removed']);
    }

    public function destroyAll(int $vendorId): JsonResponse
    {
        $this->cartRepository->removeAll($vendorId);
        return response()->json(['message' => 'Cart cleared']);
    }
}

