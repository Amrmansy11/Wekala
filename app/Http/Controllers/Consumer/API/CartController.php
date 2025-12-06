<?php

namespace App\Http\Controllers\Consumer\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consumer\Api\Cart\AddToCartRequest;
use App\Http\Requests\Consumer\Api\Cart\ShippingAddressRequest;
use App\Http\Resources\Consumer\Cart\CartShippingAddressesResource;
use App\Models\Cart;
use App\Models\CartShippingAddress;
use App\Models\User;
use App\Repositories\Consumer\CartRepository;
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
        $grouped = $this->cartRepository->getMyCartGroupedByVendorConsumer();
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
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        return CartShippingAddressesResource::collection($user->addresses);
    }

    public function shippingAddress(ShippingAddressRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $shippingAddress = CartShippingAddress::query()->updateOrCreate([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'address_type' => $request->input('address_type'),
        ], [
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

