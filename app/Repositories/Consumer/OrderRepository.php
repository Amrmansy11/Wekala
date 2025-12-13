<?php

namespace App\Repositories\Consumer;

use App\Models\Offer;
use App\Models\CartShippingAddress;
use App\Models\OrderShippingAddress;
use App\Models\User;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    protected Model $model;

    protected array $discounts = [];
    protected array $vouchers = [];
    protected array $offers = [];

    public function __construct(Order $model)
    {
        $this->model = $model;
        parent::__construct($model);
    }


    public function update(array $data, int|string $modelId): Model
    {

        return parent::update($data, $modelId);
    }


    /**
     * @throws \Throwable
     */
    public function checkout(array $request): array
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        return DB::transaction(function () use ($user, $request) {
            /** @var Cart|null $cart */
            $cart = Cart::query()
                ->with(['items' => function ($q) {
                    $q->with(['product', 'variant']);
                }])
                ->where('user_id', $user->id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => [__('validation.custom.cart.empty')],
                ]);
            }

            $ordersCreated = collect();
            $itemsGrouped = $cart->items->groupBy('vendor_id');
            $this->loadDiscounts($itemsGrouped);
            $this->loadVouchers($itemsGrouped);
            $this->loadOffers($itemsGrouped);

            $parentId = null;
            $index = 0;
            foreach ($itemsGrouped as $sellerVendorId => $items) {
                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                $discount = $this->getOrderPromotionalDiscountAmount($items, (int)$sellerVendorId);

                //                $delivery = $this->calcDeliveryFees($buyerVendor, (int)$sellerVendorId);
                $delivery = 0.0;
                $total = max(0, $subtotal - $discount) + $delivery;

                // Create order header
                $order = new Order([
                    'cart_id' => $cart->id,
                    'user_id' => $user->id,
                    'seller_vendor_id' => (int)$sellerVendorId,
                    'subtotal' => (float)$subtotal,
                    'parent_id' => $parentId,
                    'discount' => $discount,
                    'delivery' => $delivery,
                    'total' => $total,
                    'status' => 'pending',
                ]);
                $order->save();
                if ($index === 0) {
                    $parentId = $order->id;
                }
                $index++;
                /** @var CartItem $item */
                foreach ($items as $item) {
                    /** @var Product $product */
                    $product = Product::query()
                        ->where('id', $item->product_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $variant = null;
                    if (!is_null($item->product_variant_id)) {
                        /** @var ProductVariant $variant */
                        $variant = ProductVariant::query()
                            ->where('product_id', $product->id)
                            ->where('id', $item->product_variant_id)
                            ->lockForUpdate()
                            ->first();
                        if (!$variant) {
                            throw ValidationException::withMessages([
                                'product_variant_id' => [__('validation.custom.product.not_found')],
                            ]);
                        }
                        $sizeId = $item->product_size_id ?? null;
                        $validSizeIds = $variant->sizes
                            ->pluck('pivot.product_size_id')
                            ->map(fn($id) => (int)$id)
                            ->all();
                        if (!$sizeId || !in_array((int)$sizeId, $validSizeIds, true)) {
                            throw ValidationException::withMessages([
                                'product_variant_id' => [__('validation.custom.product.invalid_size')],
                            ]);
                        }
                        $availableQuantity = $variant->sizes()
                            ->where('product_size_id', $sizeId)
                            ->first()?->pivot->total_quantity ?? 0;
                        if ($availableQuantity < $item->quantity) {
                            throw ValidationException::withMessages([
                                'quantity' => [__(
                                    'validation.custom.product.quantity',
                                    ['color' => $variant->color, 'stock' => $availableQuantity],
                                )],
                            ]);
                        }
                        $variant->sizes()
                            ->updateExistingPivot($sizeId, [
                                'total_quantity' => DB::raw('total_quantity - ' . (int)$item->quantity)
                            ]);
                    } else {
                        if (is_null($product->stock_b2c) || $product->stock_b2c < $item->quantity) {
                            throw ValidationException::withMessages([
                                'quantity' => [__(
                                    'validation.custom.product.quantity',
                                    ['color' => $product->name, 'stock' => $product->_b2c],
                                )],
                            ]);
                        }
                        $product->query()->decrement('stock_b2c', $item->quantity);
                    }

                    // Calculate discount for this item
                    $itemDiscountData = $this->getItemAppliedPromotion($item, $sellerVendorId);
                    $lineTotal = (float)$item->unit_price * (int)$item->quantity;

                    $data = [
                        'order_id' => $order->id,
                        'vendor_user_id' => $item->vendor_user_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $lineTotal,
                    ];

                    $priceAfterDiscount = $item->unit_price;

                    if (!is_null($itemDiscountData)) {
                        if ($itemDiscountData['type'] === 'discount') {
                            $data = array_merge($data, [
                                'discount_id' => $itemDiscountData['id'],
                                'discount_amount' => $itemDiscountData['discount_amount'],
                                'discount_percentage' => $itemDiscountData['percentage'],
                            ]);

                            $priceAfterDiscount -= $itemDiscountData['discount_amount'];
                        }

                        if ($itemDiscountData['type'] === 'voucher') {
                            $data = array_merge($data, [
                                'voucher_id' => $itemDiscountData['id'],
                                'discount_percentage' => $itemDiscountData['percentage'],
                                'discount_amount' => $itemDiscountData['discount_amount'],
                            ]);

                            $priceAfterDiscount -= $itemDiscountData['discount_amount'];
                        }

                        if ($itemDiscountData['type'] === 'offer') {
                            $data['offer_id'] = $itemDiscountData['id'];
                        }
                    }

                    $data = array_merge($data, [
                        'price_after_discount' => $priceAfterDiscount,
                        'line_total_after_discount' => $priceAfterDiscount * $item->quantity,
                    ]);

                    OrderItem::query()->create($data);
                }

                // Clear checked-out items from cart for this seller vendor
                CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('vendor_id', (int)$sellerVendorId)
                    ->delete();
                $shippingAddress = CartShippingAddress::query()->findOrFail($request['shipping_address_id']);
                if ($shippingAddress) {
                    OrderShippingAddress::query()->create([
                        'order_id' => $order->id,
                        'address_type' => $shippingAddress->address_type,
                        'recipient_name' => $shippingAddress->recipient_name,
                        'recipient_phone' => $shippingAddress->recipient_phone,
                        'full_address' => $shippingAddress->full_address,
                        'state_id' => $shippingAddress->state_id,
                        'city_id' => $shippingAddress->city_id,
                    ]);
                }
                $ordersCreated->push($order->load('items'));
            }

            $remaining = CartItem::query()->where('cart_id', $cart->id)->count();
            if ($remaining === 0) {
                $cart->status = 'checked_out';
                $cart->save();
            }
            return ['orders' => $ordersCreated];
        });
    }

    /**
     * List orders for current buyer vendor.
     */
    /**
     * Get buyer orders with optional filters
     */
    public function myBuyerOrders(
        int     $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string  $sortDirection = 'desc',
    ): LengthAwarePaginator {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        return $this->baseOrderQuery()
            ->where('user_id', $user->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Get seller orders with optional filters
     */
    public function mySellerOrders(
        int     $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string  $sortDirection = 'desc',
    ): LengthAwarePaginator {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        return $this->baseOrderQuery()
            ->where('user_id', $user->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    public function myBuyerOrdersGroupedByVendorForConsumer(
        int $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string $sortDirection = 'desc',
    ): LengthAwarePaginator {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        $orders = $this->baseOrderQuery()
            ->where('user_id', $user->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);

        $orders->getCollection()->transform(function ($order) {
            $grouped = $order->items->groupBy('vendor_id');
            $this->loadDiscounts($grouped);

            $vendors = [];
            $totalSubtotal = 0;
            $totalDiscount = 0;
            $totalDelivery = 0;
            $totalAmount = 0;

            foreach ($grouped as $vendorId => $items) {

                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                $discount = $this->calcDiscountAmount($items, (int)$vendorId);
                $delivery = 0.0;
                $total = max(0, $subtotal - $discount) + $delivery;

                $totalSubtotal += $subtotal;
                $totalDiscount += $discount;
                $totalDelivery += $delivery;
                $totalAmount += $total;

                $vendors[] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => optional($items->first()->vendor)->store_name,
                    'items' => $items->map(function($item) use ($vendorId){
                        $discountData = $this->calcItemDiscount($item, $vendorId);
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product->name ?? null,
                            'image' => $item->product->getFirstMediaUrl('images'),
                            'variant' => $item->variant->color ?? null,
                            'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount_percentage' => $discountData['percentage'],
                            'discount_amount' => $discountData['amount'],
                            'price_after_discount' => $item->unit_price - $discountData['amount'],
                            'total_price' => $item->unit_price * $item->quantity,
                            'total_after_discount' => ($item->unit_price - $discountData['amount']) * $item->quantity,
                        ];
                    }),
                ];
            }

            $summary = [
                'subtotal' => (float)$totalSubtotal,
                'discount' => (float)$totalDiscount,
                'delivery' => (float)$totalDelivery,
                'total' => (float)$totalAmount,
            ];

            return [
                'order_id' => $order->id,
                'status' => $order->status,
                'summary' => $summary,
                'vendors' => $vendors,
            ];
        });

        return $orders;
    }


    /**
     * Find order by ID
     */
    public function show(int $orderId): \Illuminate\Database\Eloquent\Collection|Model
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        return $this->baseOrderQuery()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($orderId);
    }

    /**
     * Base query for orders
     */
    private function baseOrderQuery(): Builder
    {
        return $this->model->newQuery()
            ->with($this->getOrderRelations())
            ->whereNull('parent_id');
    }

    /**
     * Get standard order relations
     */
    private function getOrderRelations(): array
    {
        return [
            'items.product',
            'items.variant',
            'children' => function ($q) {
                $q->with([
                    'items.product',
                    'items.variant',
                    'sellerVendor',
                ]);
            },
            'sellerVendor',
            'buyerVendor',
        ];
    }

    private function calcItemDiscount(CartItem|OrderItem $item, int $vendorId): array
    {
        $discountData = ['id' => null, 'percentage' => 0.0, 'amount' => 0.0];
        $discount = $this->getItemDiscount($item, $vendorId);
        if (is_null($discount)) {
            return $discountData;
        }

        $discountData['id'] = $discount['id'];
        $discountData['percentage'] = $discount['percentage'];
        $discountData['amount'] = $item->unit_price * ($discount['percentage'] / 100);

        return $discountData;
    }

    private function getItemDiscount(CartItem|OrderItem $item, int $vendorId): ?array
    {
        return $this->discounts[$vendorId][$item->product_id] ?? null;
    }

    private function getItemVoucher(CartItem|OrderItem $item, int $vendorId): ?array
    {
        return $this->vouchers[$vendorId][$item->product_id] ?? null;
    }

    private function getVendorOffer(int $vendorId): ?Offer
    {
        return $this->offers[$vendorId] ?? null;
    }

    private function getOrderPromotionalDiscountAmount(Collection $items, int $vendorId): float
    {
        return $this->calcDiscountAmount($items, $vendorId) ?: ($this->calcVoucherDiscount($items, $vendorId) ?: $this->calcOfferDiscount($items, $vendorId));
    }

    private function getItemAppliedPromotion(CartItem|OrderItem $item, int $vendorId): ?array
    {
        $discount = $this->getItemDiscount($item, $vendorId);
        if (!is_null($discount)) {
            return ['type' => 'discount', 'id' => $discount['id'], 'percentage' => $discount['percentage'], 'discount_amount' => $item->unit_price * ($discount['percentage'] / 100)];
        }

        $voucher = $this->getItemVoucher($item, $vendorId);
        if (!is_null($voucher)) {
            $discountAmount = 0;

            if (!is_null($voucher['percentage'])) {
                $discountAmount = $item->unit_price * ($voucher['percentage'] / 100);
            }

            if (!is_null($voucher['amount'])) {
                $discountAmount = $voucher['amount'];
            }
            return ['type' => 'voucher', 'id' => $voucher['id'], 'percentage' => $voucher['percentage'], 'discount_amount' => $discountAmount];
        }

        $offer = $this->getVendorOffer($vendorId);
        if (!is_null($offer)) {
            return ['type' => 'offer', 'id' => $offer->id, 'percentage' => null, 'discount_amount' => null];
        }

        return null;
    }

    private function calcDiscountAmount(Collection $items, int $sellerVendorId): float
    {
        $totalDiscount = 0.0;

        foreach ($items as $item) {
            $itemDiscountData = $this->calcItemDiscount($item, $sellerVendorId);
            $totalDiscount += $itemDiscountData['amount'] * $item->quantity;
        }
        return $totalDiscount;
    }

    private function calcVoucherDiscount(Collection $items, int $vendorId): float
    {
        $discount = 0;

        foreach ($items as $item) {
            $voucherData = $this->getItemVoucher($item, $vendorId);
            if (is_null($voucherData)) {
                continue;
            }

            if (!is_null($voucherData['percentage'])) {
                $discount += $item->unit_price * ($voucherData['percentage'] / 100) * $item->quantity;
                continue;
            }

            if (!is_null($voucherData['amount'])) {
                $discount += $voucherData['amount'] * $item->quantity;
            }
        }

        return $discount;
    }

    private function calcOfferDiscount(Collection $items, int $vendorId): float
    {
        $discount = 0;

        if (empty($this->offers[$vendorId])) {
            return $discount;
        }

        $quantity = $items->sum('quantity');
        $originalAmount  = $items->sum(fn($i) => $i->unit_price * $i->quantity);

        foreach ($this->offers[$vendorId] as $offer) {
            if ($offer->type === 'quantity' && $quantity >= $offer->quantity) {
                return $originalAmount * ($offer->discount / 100);
            }

            if($offer->type === 'purchase' && $originalAmount >= $offer->amount) {
                return $originalAmount * ($offer->discount / 100);
            }

            if ($offer->type === 'custom' && $quantity >= $offer->buy) {
                /** @var OrderItem $lowesUnitPriceItem */
                $lowesUnitPriceItem = $items->sortBy('unit_price')->first();
                $freeItemsCount = 0;

                $i = 0;
                while ($freeItemsCount < $offer->get && $i < $lowesUnitPriceItem->quantity) {
                    $discount += $lowesUnitPriceItem->unit_price;
                    $freeItemsCount++;
                    $i++;
                }
            }
        }

        return $discount;
    }

    private function calcDeliveryFees(?Vendor $buyerVendor, int $vendorId): float
    {
        if (!$buyerVendor) {
            return 0.0;
        }

        $deliveryArea = \App\Models\DeliveryArea::query()
            ->where('vendor_id', $vendorId)
            ->where('state_id', $buyerVendor->state_id)
            ->where('city_id', $buyerVendor->city_id)
            ->first();

        return $deliveryArea ? (float)$deliveryArea->price : 0.0;
    }

    private function loadDiscounts(Collection $itemsGrouped): void
    {
        foreach ($itemsGrouped as $vendorId => $items) {
            $ids = $items->pluck('product_id');
            $discounts = Discount::query()
                ->active()
                ->join('discount_products', function($join) use ($ids) {
                    $join->on('discounts.id', '=', 'discount_products.discount_id');
                })
                ->select(DB::raw('discounts.*, discount_products.product_id as product_id'))
                ->where('vendor_id', $vendorId)
                ->whereIn('discount_products.product_id', $ids)
                ->get();

            if ($discounts->isEmpty()) {
                continue;
            }

            if (!isset($this->discounts[$vendorId])) {
                $this->discounts[$vendorId] = [];
            }

            foreach ($discounts as $discount) {
                if (!isset($this->discounts[$vendorId][$discount->product_id])) {
                    $this->discounts[$vendorId][$discount->product_id] = ['id' => $discount->id, 'percentage' => $discount->percentage];
                    continue;
                }

                if ($discount->percentage > $this->discounts[$vendorId][$discount->product_id]['percentage']) {
                    $this->discounts[$vendorId][$discount->product_id] = ['id' => $discount->id, 'percentage' => $discount->percentage];
                }
            }
        }
    }
    private function loadVouchers(Collection $itemsGrouped): void
    {
        foreach ($itemsGrouped as $vendorId => $items) {
            $ids = $items->pluck('product_id');
            $vouchers = Voucher::query()
                ->active()
                ->join('voucher_product', function($join) use ($ids) {
                    $join->on('vouchers.id', '=', 'voucher_product.voucher_id');
                })
                ->select(DB::raw('vouchers.*, voucher_product.product_id as product_id'))
                ->where('vendor_id', $vendorId)
                ->whereIn('voucher_product.product_id', $ids)
                ->get();

            if ($vouchers->isEmpty()) {
                continue;
            }

            if (!isset($this->vouchers[$vendorId])) {
                $this->vouchers[$vendorId] = [];
            }

            foreach ($vouchers as $voucher) {
                if (!isset($this->vouchers[$vendorId][$voucher->product_id])) {
                    $this->vouchers[$vendorId][$voucher->product_id] = ['id' => $voucher->id, 'percentage' => $voucher->percentage, 'amount' => $voucher->amount];
                    continue;
                }

                if (!is_null($voucher->percentage) && $voucher->percentage > $this->vouchers[$vendorId][$voucher->product_id]['percentage'] ||
                    !is_null($voucher->amount) && $voucher->amount > $this->vouchers[$vendorId][$voucher->product_id]['amount']
                ) {
                    $this->vouchers[$vendorId][$voucher->product_id] = ['id' => $voucher->id, 'percentage' => $voucher->percentage, 'amount' => $voucher->amount];
                }
            }
        }
    }
    private function loadOffers(Collection $itemsGrouped): void
    {
        foreach ($itemsGrouped as $vendorId => $items) {
            $offers = Offer::query()
                ->active()
                ->where('vendor_id', $vendorId)
                ->get();

            if ($offers->isEmpty()) {
                continue;
            }

            if (!isset($this->offers[$vendorId])) {
                $this->offers[$vendorId] = [];
            }

            foreach ($offers as $offer) {
                $this->offers[$vendorId][] = $offer;
            }
        }
    }
}
