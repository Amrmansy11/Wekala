<?php

namespace App\Repositories\Consumer;

use Exception;
use App\Models\Cart;
use App\Models\User;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\CartShippingAddress;
use App\Models\OrderShippingAddress;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository
{
    protected Model $model;

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
     * Checkout the current cart and create orders grouped by seller vendor.
     * Ensures concurrency-safety by locking product/variant rows during stock deduction.
     */
    public function checkout(array $request): array
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        return DB::transaction(function () use ($user, $request) {
            $cart = $this->getCartWithLock($user->id);
            $itemsGrouped = $cart->items->groupBy('vendor_id');
            $ordersCreated = collect();
            $parentId = null;

            foreach ($itemsGrouped as $sellerVendorId => $items) {
                $discountDecision = $this->getBestDiscountDecision($items, (int)$sellerVendorId);

                $order = $this->createOrderHeader(
                    $cart,
                    $user,
                    $items,
                    $sellerVendorId,
                    $discountDecision,
                    $parentId
                );

                if (!$parentId) {
                    $parentId = $order->id;
                }

                $this->processOrderItems($order, $items, $discountDecision);
                $this->clearCartItems($cart->id, $sellerVendorId);
                $this->createShippingAddress($order->id, $request['shipping_address_id'] ?? 0);

                $ordersCreated->push($order->load('items'));
            }

            $this->closeCartIfEmpty($cart);

            return ['orders' => $ordersCreated];
        });
    }

    /**
     * Get buyer orders with optional filters
     */
    public function myBuyerOrders(
        int $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        return $this->getUserOrders($perPage, $status, $sortBy, $sortDirection);
    }

    /**
     * Get seller orders with optional filters
     */
    public function mySellerOrders(
        int $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        return $this->getUserOrders($perPage, $status, $sortBy, $sortDirection);
    }

    /**
     * Get buyer orders grouped by vendor for consumer
     */
    public function myBuyerOrdersGroupedByVendorForConsumer(
        int $perPage = 15,
        ?string $status = null,
        ?string $sortBy = 'id',
        string $sortDirection = 'desc'
    ): LengthAwarePaginator {
        $orders = $this->getUserOrders($perPage, $status, $sortBy, $sortDirection);

        $orders->getCollection()->transform(function ($order) {
            return $this->transformOrderWithVendorGrouping($order);
        });

        return $orders;
    }

    /**
     * Find order by ID
     */
    public function show(int $orderId): Model
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        return $this->baseOrderQuery()
            ->where('user_id', $user->id)
            ->findOrFail($orderId);
    }

    // ==================== Private Helper Methods ====================

    /**
     * Get cart with lock for transaction safety
     */
    private function getCartWithLock(int $userId): Cart
    {
        /** @var Cart|null $cart */
        $cart = Cart::query()
            ->with(['items' => fn($q) => $q->with(['product', 'variant'])])
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->lockForUpdate()
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => [__('validation.custom.cart.empty')],
            ]);
        }

        return $cart;
    }

    /**
     * Create order header
     */
    private function createOrderHeader(
        Cart $cart,
        User $user,
        Collection $items,
        int $sellerVendorId,
        array $discountDecision,
        ?int $parentId
    ): Order {
        $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
        $delivery = 0.0;
        $total = max(0, $subtotal - $discountDecision['amount']) + $delivery;

        $order = new Order([
            'cart_id' => $cart->id,
            'user_id' => $user->id,
            'seller_vendor_id' => $sellerVendorId,
            'subtotal' => (float)$subtotal,
            'parent_id' => $parentId,
            'discount_type' => $discountDecision['type'],
            'discount' => $discountDecision['amount'],
            'delivery' => $delivery,
            'total' => $total,
            'status' => 'pending',
        ]);

        $order->save();
        return $order;
    }

    /**
     * Process all order items with stock validation and discount calculation
     */
    private function processOrderItems(Order $order, Collection $items, array $discountDecision): void
    {
        foreach ($items as $item) {
            $this->validateAndUpdateStock($item);
            $itemDiscountData = $this->calculateItemDiscount($item, $items, $discountDecision);
            $this->createOrderItem($order, $item, $itemDiscountData);
        }
    }

    /**
     * Validate stock and update quantities with proper locking
     */
    private function validateAndUpdateStock(CartItem $item): void
    {
        /** @var Product $product */
        $product = Product::query()
            ->where('id', $item->product_id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($item->product_variant_id) {
            $this->validateAndUpdateVariantStock($product, $item);
        } else {
            $this->validateAndUpdateProductStock($product, $item);
        }
    }

    /**
     * Validate and update variant stock
     */
    private function validateAndUpdateVariantStock(Product $product, CartItem $item): void
    {
        /** @var ProductVariant $variant */
        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('id', $item->product_variant_id)
            ->lockForUpdate()
            ->firstOrFail();

        $sizeId = $item->product_size_id;
        $this->validateVariantSize($variant, $sizeId);

        $availableQuantity = $variant->sizes()
            ->where('product_size_id', $sizeId)
            ->first()?->pivot->total_quantity ?? 0;

        if ($availableQuantity < $item->quantity) {
            throw ValidationException::withMessages([
                'quantity' => [__(
                    'validation.custom.product.quantity',
                    ['color' => $variant->color, 'stock' => $availableQuantity]
                )],
            ]);
        }

        $variant->sizes()->updateExistingPivot($sizeId, [
            'total_quantity' => DB::raw('total_quantity - ' . (int)$item->quantity)
        ]);
    }

    /**
     * Validate variant size
     */
    private function validateVariantSize(ProductVariant $variant, ?int $sizeId): void
    {
        $validSizeIds = $variant->sizes
            ->pluck('pivot.product_size_id')
            ->map(fn($id) => (int)$id)
            ->all();

        if (!$sizeId || !in_array((int)$sizeId, $validSizeIds, true)) {
            throw ValidationException::withMessages([
                'product_variant_id' => [__('validation.custom.product.invalid_size')],
            ]);
        }
    }

    /**
     * Validate and update product stock
     */
    private function validateAndUpdateProductStock(Product $product, CartItem $item): void
    {
        if (is_null($product->stock_b2c) || $product->stock_b2c < $item->quantity) {
            throw ValidationException::withMessages([
                'quantity' => [__(
                    'validation.custom.product.quantity',
                    ['color' => $product->name, 'stock' => $product->stock_b2c]
                )],
            ]);
        }

        $product->query()->decrement('stock_b2c', $item->quantity);
    }

    /**
     * Calculate item-level discount based on decision type
     */
    private function calculateItemDiscount(
        CartItem $item,
        Collection $allItems,
        array $discountDecision
    ): array {
        $discountType = $discountDecision['type'];

        return match($discountType) {
            'discount' => $this->calcItemDiscountFromCollection($item, $discountDecision['discounts']),
            'voucher' => $this->calcVoucherForItem($item, $discountDecision['vouchers']),
            'offer' => $this->calcOfferForItem($item, $allItems, $discountDecision['offers']),
            default => ['percentage' => 0.0, 'amount' => 0.0, 'id' => null],
        };
    }

    /**
     * Create order item record
     */
    private function createOrderItem(Order $order, CartItem $item, array $discountData): void
    {
        $lineTotal = (float)$item->unit_price * (int)$item->quantity;
        $priceAfterDiscount = $item->unit_price - $discountData['amount'];
        $lineTotalAfterDiscount = $priceAfterDiscount * $item->quantity;

        OrderItem::query()->create([
            'order_id' => $order->id,
            'vendor_user_id' => $item->vendor_user_id,
            'vendor_id' => $item->vendor_id,
            'product_id' => $item->product_id,
            'product_variant_id' => $item->product_variant_id,
            'discount_id' => $discountData['discount_id'] ?? null,
            'voucher_id' => $discountData['voucher_id'] ?? null,
            'offer_id' => $discountData['offer_id'] ?? null,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'discount_percentage' => $discountData['percentage'],
            'discount_amount' => $discountData['amount'],
            'price_after_discount' => $priceAfterDiscount,
            'line_total' => $lineTotal,
            'line_total_after_discount' => $lineTotalAfterDiscount,
        ]);
    }

    /**
     * Clear cart items for vendor
     */
    private function clearCartItems(int $cartId, int $vendorId): void
    {
        CartItem::query()
            ->where('cart_id', $cartId)
            ->where('vendor_id', $vendorId)
            ->delete();
    }

    /**
     * Create shipping address for order
     */
    private function createShippingAddress(int $orderId, int $shippingAddressId): void
    {
        $shippingAddress = CartShippingAddress::query()->find($shippingAddressId);

        if ($shippingAddress) {
            OrderShippingAddress::query()->create([
                'order_id' => $orderId,
                'address_type' => $shippingAddress->address_type,
                'recipient_name' => $shippingAddress->recipient_name,
                'recipient_phone' => $shippingAddress->recipient_phone,
                'full_address' => $shippingAddress->full_address,
                'state_id' => $shippingAddress->state_id,
                'city_id' => $shippingAddress->city_id,
            ]);
        }
    }

    /**
     * Close cart if empty
     */
    private function closeCartIfEmpty(Cart $cart): void
    {
        $remaining = CartItem::query()->where('cart_id', $cart->id)->count();

        if ($remaining === 0) {
            $cart->status = 'checked_out';
            $cart->save();
        }
    }

    /**
     * Get user orders with filters (unified for buyer/seller)
     */
    private function getUserOrders(
        int $perPage,
        ?string $status,
        ?string $sortBy,
        string $sortDirection
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
     * Transform order with vendor grouping
     */
    private function transformOrderWithVendorGrouping($order): array
    {
        $grouped = $order->items->groupBy('vendor_id');
        $vendors = [];
        $totals = ['subtotal' => 0, 'discount' => 0, 'delivery' => 0, 'total' => 0];

        foreach ($grouped as $vendorId => $items) {
            $vendorData = $this->calculateVendorTotals($items, (int)$vendorId);
            $vendors[] = $vendorData;

            $totals['subtotal'] += $vendorData['subtotal'];
            $totals['discount'] += $vendorData['discount'];
            $totals['delivery'] += $vendorData['delivery'];
            $totals['total'] += $vendorData['total'];
        }

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'summary' => $totals,
            'vendors' => $vendors,
        ];
    }

    /**
     * Calculate vendor totals and format items
     */
    private function calculateVendorTotals(Collection $items, int $vendorId): array
    {
        $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
        $discount = $this->getTotalDiscountAmount($items, $vendorId);
        $delivery = 0.0;
        $total = max(0, $subtotal - $discount) + $delivery;
        return [
            'vendor_id' => $vendorId,
            'vendor_name' => optional($items->first()->vendor)->store_name,
            'subtotal' => (float)$subtotal,
            'discount' => (float)$discount,
            'delivery' => (float)$delivery,
            'total' => (float)$total,
            'items' => $this->formatVendorItems($items, $vendorId),
        ];
    }

    /**
     * Format vendor items for response
     */
    private function formatVendorItems(Collection $items, int $vendorId): array
    {
        return $items->map(function ($item) use ($vendorId) {
            $itemDiscount = $this->calcItemDiscountLegacy($item, $vendorId);

            return [
                'id' => $item->id,
                'product_name' => $item->product->name ?? null,
                'image' => $item->product->getFirstMediaUrl('images'),
                'variant' => $item->variant->color ?? null,
                'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'discount_percentage' => $itemDiscount['percentage'],
                'discount_amount' => $itemDiscount['amount'],
                'price_after_discount' => $item->unit_price - $itemDiscount['amount'],
                'total_price' => $item->unit_price * $item->quantity,
                'total_after_discount' => ($item->unit_price - $itemDiscount['amount']) * $item->quantity,
            ];
        })->toArray();
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
            'children' => fn($q) => $q->with([
                'items.product',
                'items.variant',
                'sellerVendor',
            ]),
            'sellerVendor',
            'buyerVendor',
        ];
    }

    // ==================== Discount Calculation Methods ====================

    /**
     * Get best discount decision with all data in single pass
     */
    private function getBestDiscountDecision(Collection $items, int $vendorId): array
    {
        $discounts = $this->fetchActiveDiscounts($vendorId);
        $vouchers = $this->fetchActiveVouchers($vendorId);
        $offers = $this->fetchActiveOffers($vendorId);

        $amounts = [
            'discount' => $this->getTotalDiscountFromCollection($items, $discounts),
            'voucher' => $this->getTotalVoucherDiscount($items, $vouchers),
            'offer' => $this->getTotalOfferDiscount($items, $offers),
        ];

        $bestType = array_keys($amounts, max($amounts))[0] ?? null;
        $bestAmount = max($amounts);

        return [
            'type' => $bestAmount > 0 ? $bestType : null,
            'amount' => (float)$bestAmount,
            'discounts' => $discounts,
            'vouchers' => $vouchers,
            'offers' => $offers,
        ];
    }

    /**
     * Fetch active discounts for vendor
     */
    private function fetchActiveDiscounts(int $vendorId): Collection
    {
        return Discount::query()
            ->active()
            ->where('vendor_id', $vendorId)
            ->with('products')
            ->get();
    }

    /**
     * Fetch active vouchers for vendor
     */
    private function fetchActiveVouchers(int $vendorId): Collection
    {
        return Voucher::query()
            ->where('vendor_id', $vendorId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    /**
     * Fetch active offers for vendor
     */
    private function fetchActiveOffers(int $vendorId): Collection
    {
        return Offer::query()
            ->where('vendor_id', $vendorId)
            ->where('start', '<=', now())
            ->where('end', '>=', now())
            ->get();
    }

    /**
     * Calculate item discount from collection
     */
    private function calcItemDiscountFromCollection($item, Collection $discounts): array
    {
        if ($discounts->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0, 'discount_id' => null];
        }

        $applicable = $discounts->filter(
            fn($d) => $d->products->contains('id', $item->product_id)
        );

        if ($applicable->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0, 'discount_id' => null];
        }

        $best = $applicable->sortByDesc('percentage')->first();
        $percentage = $best->percentage ?? 0.0;
        $amount = $item->unit_price * ($percentage / 100);

        return [
            'percentage' => (float)$percentage,
            'amount' => (float)$amount,
            'discount_id' => $best->id,
        ];
    }

    /**
     * Calculate total discount from collection
     */
    private function getTotalDiscountFromCollection(Collection $items, Collection $discounts): float
    {
        if ($discounts->isEmpty()) {
            return 0.0;
        }

        return $items->sum(function ($item) use ($discounts) {
            $itemData = $this->calcItemDiscountFromCollection($item, $discounts);
            return $itemData['amount'] * $item->quantity;
        });
    }

    /**
     * Calculate voucher for item
     */
    private function calcVoucherForItem(CartItem $item, Collection $vouchers): array
    {
        if ($vouchers->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0, 'voucher_id' => null];
        }

        $applicable = $vouchers->filter(function ($v) use ($item) {
            if ($v->for_all) return true;
            return $v->products()->where('product_id', $item->product_id)->exists();
        });

        if ($applicable->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0, 'voucher_id' => null];
        }

        $best = $applicable->map(function ($v) use ($item) {
            $amount = !is_null($v->percentage)
                ? $item->unit_price * ($v->percentage / 100)
                : (float)$v->amount;

            return [
                'amount' => $amount,
                'percentage' => $v->percentage ?? 0.0,
                'voucher_id' => $v->id,
            ];
        })->sortByDesc('amount')->first();

        return $best ?: ['percentage' => 0.0, 'amount' => 0.0, 'voucher_id' => null];
    }

    /**
     * Calculate total voucher discount
     */
    private function getTotalVoucherDiscount(Collection $items, Collection $vouchers): float
    {
        if ($vouchers->isEmpty()) {
            return 0.0;
        }

        return $items->sum(function ($item) use ($vouchers) {
            $data = $this->calcVoucherForItem($item, $vouchers);
            return $data['amount'] * $item->quantity;
        });
    }

    /**
     * Calculate offer for item
     */
    private function calcOfferForItem(
        CartItem $item,
        Collection $allItems,
        Collection $offers
    ): array {
        if ($offers->isEmpty()) {
            return ['amount' => 0.0, 'offer_id' => null, 'percentage' => 0.0];
        }

        $quantity = $allItems->sum('quantity');
        $originalAmount = $allItems->sum(fn($i) => $i->unit_price * $i->quantity);

        foreach ($offers as $offer) {
            $result = match($offer->type) {
                'quantity' => $this->applyQuantityOffer($offer, $quantity, $item),
                'purchase' => $this->applyPurchaseOffer($offer, $originalAmount, $item),
                'custom' => $this->applyCustomOffer($offer, $quantity, $allItems, $item),
                default => null,
            };

            if ($result) {
                return $result;
            }
        }

        return ['amount' => 0.0, 'offer_id' => null, 'percentage' => 0.0];
    }

    /**
     * Apply quantity-based offer
     */
    private function applyQuantityOffer($offer, int $quantity, CartItem $item): ?array
    {
        if ($quantity >= $offer->quantity) {
            return [
                'amount' => $item->unit_price * ($offer->discount / 100),
                'offer_id' => $offer->id,
                'percentage' => $offer->discount,
            ];
        }
        return null;
    }

    /**
     * Apply purchase amount-based offer
     */
    private function applyPurchaseOffer($offer, float $amount, CartItem $item): ?array
    {
        if ($amount >= $offer->amount) {
            return [
                'amount' => $item->unit_price * ($offer->discount / 100),
                'offer_id' => $offer->id,
                'percentage' => $offer->discount,
            ];
        }
        return null;
    }

    /**
     * Apply custom buy X get Y offer
     */
    private function applyCustomOffer(
        $offer,
        int $quantity,
        Collection $allItems,
        CartItem $item
    ): ?array {
        if ($quantity < $offer->buy) {
            return null;
        }

        $sortedItems = $allItems->sortBy('unit_price');
        $freeLeft = $offer->get;

        foreach ($sortedItems as $si) {
            for ($i = 0; $i < $si->quantity; $i++) {
                if ($freeLeft <= 0) break 2;

                if ($si->id === $item->id) {
                    return [
                        'amount' => $item->unit_price,
                        'offer_id' => $offer->id,
                        'percentage' => 100.0,
                    ];
                }
                $freeLeft--;
            }
        }

        return null;
    }

    /**
     * Calculate total offer discount
     */
    private function getTotalOfferDiscount(Collection $items, Collection $offers): float
    {
        if ($offers->isEmpty()) {
            return 0.0;
        }

        $quantity = $items->sum('quantity');
        $originalAmount = $items->sum(fn($i) => $i->unit_price * $i->quantity);

        foreach ($offers as $offer) {
            $result = match($offer->type) {
                'quantity' => $quantity >= $offer->quantity
                    ? $originalAmount * ($offer->discount / 100)
                    : 0,
                'purchase' => $originalAmount >= $offer->amount
                    ? $originalAmount * ($offer->discount / 100)
                    : 0,
                'custom' => $this->calculateCustomOfferTotal($offer, $quantity, $items),
                default => 0,
            };

            if ($result > 0) {
                return $result;
            }
        }

        return 0.0;
    }

    /**
     * Calculate custom offer total
     */
    private function calculateCustomOfferTotal($offer, int $quantity, Collection $items): float
    {
        if ($quantity < $offer->buy) {
            return 0.0;
        }

        $sortedItems = $items->sortBy('unit_price');
        $freeItemsCount = 0;
        $discountAmount = 0.0;

        foreach ($sortedItems as $si) {
            for ($i = 0; $i < $si->quantity; $i++) {
                if ($freeItemsCount >= $offer->get) {
                    break 2;
                }
                $discountAmount += $si->unit_price;
                $freeItemsCount++;
            }
        }

        return $discountAmount;
    }

    /**
     * Get total discount amount (used in summaries) - fetches from DB
     */
    private function getTotalDiscountAmount(Collection $items, int $vendorId): float
    {
        $discounts = $this->fetchActiveDiscounts($vendorId);
        return $this->getTotalDiscountFromCollection($items, $discounts);
    }

    /**
     * Legacy method for backward compatibility
     */
    private function calcItemDiscountLegacy($item, $vendorId): array
    {
        $discounts = $this->fetchActiveDiscounts($vendorId);
        return $this->calcItemDiscountFromCollection($item, $discounts);
    }
}
