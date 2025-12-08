<?php

namespace App\Repositories\Vendor;

use App\Models\OrderShippingAddress;
use Exception;
use App\Models\Cart;
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
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        return DB::transaction(function () use ($vendorUser) {
            /** @var Cart|null $cart */
            $cart = Cart::query()
                ->with(['items' => function ($q) {
                    $q->with(['product', 'variant']);
                }])
                ->where('vendor_id', $vendorUser->vendor_id)
                ->where('status', 'open')
                ->lockForUpdate()
                ->first();
            if (!$cart || $cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => [__('validation.custom.cart.empty')],
                ]);
            }

            $ordersCreated = collect();
            $itemsGrouped = $cart->items->groupBy('vendor_id'); // seller vendor id on each item
            $parentId = null;
            $index = 0;
            foreach ($itemsGrouped as $sellerVendorId => $items) {
                // Calculate amounts
                $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
                //                $discount = $this->calcVoucherDiscount($items, (int)$sellerVendorId);
                //                $delivery = $this->calcDeliveryFees($buyerVendor, (int)$sellerVendorId);
                $discount = 0.0;
                $delivery = 0.0;
                $total = max(0, $subtotal - $discount) + $delivery;

                // Create order header
                $order = new Order([
                    'cart_id' => $cart->id,
                    'buyer_vendor_id' => $vendorUser->vendor_id,
                    'seller_vendor_id' => (int)$sellerVendorId,
                    'vendor_user_id' => $vendorUser->id,
                    'subtotal' => (float)$subtotal,
                    'parent_id' => $parentId,
                    'discount' => (float)$discount,
                    'delivery' => (float)$delivery,
                    'total' => (float)$total,
                    'status' => 'pending',
                ]);
                $order->save();
                if ($index === 0) {
                    $parentId = $order->id;
                }
                $index++;
                // Create items and deduct stock with locks
                /** @var CartItem $item */
                foreach ($items as $item) {
                    // Lock product (and variant if exists) for update
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
                        $availableQuantity = ($variant->quantity_b2b ?? 0);
                        if ($availableQuantity < $item->quantity) {
                            throw ValidationException::withMessages([
                                'quantity' => [__(
                                    'validation.custom.product.quantity',
                                    ['color' => $variant->color, 'stock' => $availableQuantity]
                                )],
                            ]);
                        }
                    } else {
                        if (is_null($product->stock_b2b) || $product->stock_b2b < $item->quantity) {
                            throw ValidationException::withMessages([
                                'quantity' => [__(
                                    'validation.custom.product.quantity',
                                    ['color' => $product->name, 'stock' => $product->_b2b]
                                )],
                            ]);
                        }
                    }

                    // Deduct stock from appropriate fields based on product type
                    if ($variant) {
                        // Deduct from B2B fields
                        $piecesPerQuantity = $variant->total_pieces_b2b > 0 && $variant->quantity_b2b > 0
                            ? $variant->total_pieces_b2b / $variant->quantity_b2b
                            : 0;
                        if ($piecesPerQuantity > 0) {
                            $variant->query()->decrement('total_pieces_b2b', $piecesPerQuantity * $item->quantity);
                        }
                        $variant->query()->decrement('quantity_b2b', $item->quantity);
                        //                        } else {
                        //                            // Deduct from B2C fields
                        //                            $piecesPerQuantity = $variant->total_pieces_b2c > 0 && $variant->quantity_b2c > 0
                        //                                ? $variant->total_pieces_b2c / $variant->quantity_b2c
                        //                                : 0;
                        //                            if ($piecesPerQuantity > 0) {
                        //                                $variant->decrement('total_pieces_b2c', $piecesPerQuantity * $item->quantity);
                        //                            }
                        //                            $variant->decrement('quantity_b2c', $item->quantity);
                        //                        }
                    } else {
                        $product->query()->decrement('stock', $item->quantity);
                        $product->query()->decrement('stock_b2b', $item->quantity);
                    }

                    // Create order item
                    $lineTotal = (float)$item->unit_price * (int)$item->quantity;
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'vendor_user_id' => $item->vendor_user_id,
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $lineTotal,
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

            // If cart is now empty, mark as checked_out
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
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        return $this->baseOrderQuery()
            ->where('vendor_user_id', $vendorUser->id)
            ->where('buyer_vendor_id', $vendorUser->vendor_id)
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
        bool $b2bOnly = true,
    ): LengthAwarePaginator {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        $query = $this->baseOrderQuery()
            ->where('seller_vendor_id', $vendorUser->vendor_id)
            ->when($status, fn($q) => $q->where('status', $status));

        if ($b2bOnly) {
            $query->whereNotNull('buyer_vendor_id');
        }


        return $query->orderBy($sortBy, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Find order by ID
     */
    public function show(int $orderId): Order
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        return $this->baseOrderQuery()
            ->where(function ($query) use ($vendorUser) {
                $query->where('buyer_vendor_id', $vendorUser->vendor_id)
                    ->orWhere('seller_vendor_id', $vendorUser->vendor_id);
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
