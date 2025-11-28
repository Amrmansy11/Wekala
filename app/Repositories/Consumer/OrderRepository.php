<?php

namespace App\Repositories\Consumer;

use App\Models\OrderShippingAddress;
use App\Models\User;
use Exception;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\VendorUser;
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
     *
     * @return array{orders: Collection}
     * @throws Exception
     */
    public function checkout(): array
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        return DB::transaction(function () use ($user) {
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
            $parentId = null;
            $index = 0;
            foreach ($itemsGrouped as $sellerVendorId => $items) {
                $activeDiscounts = Discount::query()
                    ->active()
                    ->where('vendor_id', $sellerVendorId)
                    ->with('products')
                    ->get();

                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                $discount = $this->calcDiscountAmount($items, $activeDiscounts);
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
                                    ['color' => $variant->color, 'stock' => $availableQuantity]
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
                                    ['color' => $product->name, 'stock' => $product->_b2c]
                                )],
                            ]);
                        }
                        $product->query()->decrement('stock_b2c', $item->quantity);
                    }

                    // Calculate discount for this item
                    $itemDiscountData = $this->calcItemDiscount($item, $activeDiscounts);
                    $lineTotal = (float)$item->unit_price * (int)$item->quantity;
                    $priceAfterDiscount = $item->unit_price - $itemDiscountData['amount'];
                    $lineTotalAfterDiscount = $priceAfterDiscount * $item->quantity;
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'vendor_user_id' => $item->vendor_user_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'discount_id' => $itemDiscountData['discount_id'],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $itemDiscountData['percentage'],
                        'discount_amount' => $itemDiscountData['amount'],
                        'price_after_discount' => $priceAfterDiscount,
                        'line_total' => $lineTotal,
                        'line_total_after_discount' => $lineTotalAfterDiscount,
                    ]);
                }

                // Clear checked-out items from cart for this seller vendor
                CartItem::query()
                    ->where('cart_id', $cart->id)
                    ->where('vendor_id', (int)$sellerVendorId)
                    ->delete();
                if ($cart->shippingAddress) {
                    OrderShippingAddress::query()->create([
                        'order_id' => $order->id,
                        'address_type' => $cart->shippingAddress->address_type,
                        'recipient_name' => $cart->shippingAddress->recipient_name,
                        'recipient_phone' => $cart->shippingAddress->recipient_phone,
                        'full_address' => $cart->shippingAddress->full_address,
                        'state_id' => $cart->shippingAddress->state_id,
                        'city_id' => $cart->shippingAddress->city_id,
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
        string  $sortDirection = 'desc'
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
        string  $sortDirection = 'desc'
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
        string $sortDirection = 'desc'
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

            $vendors = [];
            $totalSubtotal = 0;
            $totalDiscount = 0;
            $totalDelivery = 0;
            $totalAmount = 0;

            foreach ($grouped as $vendorId => $items) {
                $activeDiscounts = Discount::query()
                    ->active()
                    ->where('vendor_id', $vendorId)
                    ->with('products')
                    ->get();

                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                $discount = $this->calcDiscountAmount($items, $activeDiscounts);
                $delivery = 0.0;
                $total = max(0, $subtotal - $discount) + $delivery;

                $totalSubtotal += $subtotal;
                $totalDiscount += $discount;
                $totalDelivery += $delivery;
                $totalAmount += $total;

                $vendors[] = [
                    'vendor_id' => $vendorId,
                    'vendor_name' => optional($items->first()->vendor)->store_name,
                    'items' => $items->map(fn($item) => [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? null,
                        'image' => $item->product->getFirstMediaUrl('images'),
                        'variant' => $item->variant->color ?? null,
                        'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $this->calcItemDiscount($item, $activeDiscounts)['percentage'],
                        'discount_amount' => $this->calcItemDiscount($item, $activeDiscounts)['amount'],
                        'price_after_discount' => $item->unit_price - $this->calcItemDiscount($item, $activeDiscounts)['amount'],
                        'total_price' => $item->unit_price * $item->quantity,
                        'total_after_discount' => ($item->unit_price - $this->calcItemDiscount($item, $activeDiscounts)['amount']) * $item->quantity,
                    ]),
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

    /**
     * Calculate discount for a single cart item.
     *
     * @param CartItem $item Cart item to calculate discount for
     * @param Collection $activeDiscounts Collection of active discounts
     * @return array ['discount_id' => int|null, 'percentage' => float, 'amount' => float]
     */
    private function calcItemDiscount(CartItem|OrderItem $item, Collection $activeDiscounts): array
    {
        if ($activeDiscounts->isEmpty()) {
            return ['discount_id' => null, 'percentage' => 0.0, 'amount' => 0.0];
        }
        $applicableDiscounts = $activeDiscounts->filter(function ($discount) use ($item) {
            return $discount->products->contains('id', $item->product_id);
        });

        if ($applicableDiscounts->isEmpty()) {
            return ['discount_id' => null, 'percentage' => 0.0, 'amount' => 0.0];
        }

        /** @var Discount $bestDiscount */
        $bestDiscount = $applicableDiscounts->sortByDesc('percentage')->first();
        $maxDiscountPercentage = $bestDiscount->percentage ?? 0.0;
        $discountAmount = $item->unit_price * ($maxDiscountPercentage / 100);
        return [
            'discount_id' => $bestDiscount->id,
            'percentage' => $maxDiscountPercentage,
            'amount' => (float)$discountAmount,
        ];
    }

    /**
     * Calculate total discount amount from the discounts table for cart items.
     *
     * @param Collection $items Cart items to calculate discount for
     * @param Collection $activeDiscounts Collection of active discounts
     * @return float Total discount amount
     */
    private function calcDiscountAmount(Collection $items, Collection $activeDiscounts): float
    {
        $totalDiscount = 0.0;
        if ($activeDiscounts->isEmpty()) {
            return $totalDiscount;
        }
        foreach ($items as $item) {
            $itemDiscountData = $this->calcItemDiscount($item, $activeDiscounts);
            $totalDiscount += $itemDiscountData['amount'] * $item->quantity;
        }
        return $totalDiscount;
    }

    private function calcVoucherDiscount($items, int $vendorId): float

    {
        $discount = 0.0;
        $activeVouchers = Voucher::query()
            ->where('vendor_id', $vendorId)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();

        foreach ($items as $item) {
            $applicable = $activeVouchers->filter(function ($v) use ($item) {
                if ($v->for_all) {
                    return true;
                }
                return $v->products()->where('product_id', $item->product_id)->exists();
            });
            if ($applicable->isEmpty()) {
                continue;
            }
            $best = $applicable->map(function ($v) use ($item) {
                if (!is_null($v->percentage)) {
                    return $item->unit_price * ($v->percentage / 100);
                }
                if (!is_null($v->amount)) {
                    return (float)$v->amount;
                }
                return 0.0;
            })->max() ?? 0.0;
            $discount += $best * $item->quantity;
        }
        return (float)$discount;
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
}
