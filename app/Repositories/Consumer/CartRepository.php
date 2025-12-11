<?php

namespace App\Repositories\Consumer;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Voucher;
use App\Models\DeliveryArea;
use App\Repositories\BaseRepository;
use App\Repositories\Vendor\ProductRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartRepository extends BaseRepository
{
    protected Model $model;

    public function __construct(Cart $model, private readonly ProductRepository $productRepository)
    {
        $this->model = $model;
        parent::__construct($model);
    }

    /**
     * @throws ValidationException
     */
    public function addItem(array $data): Cart
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $cart = $this->model->query()
            ->firstOrCreate([
                'user_id' => $user->id,
                'status' => 'open',
            ]);
        /** @var Product $product */
        $product = $this->productRepository->find($data['product_id']);
        $unitPrice = $product->consumer_price;
        $product->refresh();
        $variantPayload = collect($data['product_variant_id'] ?? []);
        if ($product->variants()->count() && $variantPayload->count()) {
            $variantIds = $variantPayload->pluck('id')
                ->filter()
                ->unique();

            $variants = ProductVariant::query()
                ->with('sizes')
                ->where('product_id', $product->id)
                ->whereIn('id', $variantIds)
                ->get()
                ->keyBy('id');

            foreach ($variantPayload as $variant) {
                $variantId = (int)($variant['id'] ?? 0);
                /** @var ProductVariant|null $productVariant */
                $productVariant = $variants->get($variantId);
                if (!$productVariant) {
                    throw ValidationException::withMessages([
                        'product_variant_id' => [__('validation.custom.product.not_found')],
                    ]);
                }
                $sizeId = $variant['size_id'] ?? null;
                $validSizeIds = $productVariant->sizes
                    ->pluck('pivot.product_size_id')
                    ->map(fn($id) => (int)$id)
                    ->all();
                if (!$sizeId || !in_array((int)$sizeId, $validSizeIds, true)) {
                    throw ValidationException::withMessages([
                        'product_variant_id' => [__('validation.custom.product.invalid_size')],
                    ]);
                }
                $requestedQuantity = (int)($variant['quantity'] ?? 0);
                $availableQuantity = $productVariant->sizes()->where('product_size_id', $sizeId)->first()?->pivot->total_quantity ?? 0;
                if ($availableQuantity < $requestedQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [__(
                            'validation.custom.product.quantity',
                            ['color' => $productVariant->color, 'stock' => $availableQuantity]
                        )],
                    ]);
                }
            }

            DB::transaction(function () use ($variantPayload, $unitPrice, $product, $cart, $user) {
                foreach ($variantPayload as $variant) {
                    $productVariant = ProductVariant::query()
                        ->where('product_id', $product->id)
                        ->find((int)$variant['id']);
                    $cartItem = CartItem::query()->firstOrNew([
                        'cart_id' => $cart->id,
                        'vendor_id' => $product->vendor_id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant['id'],
                        'product_size_id' => $variant['size_id'],
                        'color' => $productVariant->color,
                    ]);
                    $cartItem->quantity = $variant['quantity'];
                    $cartItem->unit_price = $unitPrice;
                    $cartItem->save();
                }
            });
        } else {
            $availableStock = $product->stock_b2c ?? 0;
            if ($availableStock < (int)$data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => [__(
                        'validation.custom.product.quantity',
                        ['color' => $product->name, 'stock' => $availableStock]
                    )],
                ]);
            }
            DB::transaction(function () use ($data, $unitPrice, $product, $cart, $user) {
                $cartItem = CartItem::query()->firstOrNew([
                    'cart_id' => $cart->id,
                    'vendor_id' => $product->vendor_id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                ]);
                $cartItem->quantity = $data['quantity'];
                $cartItem->unit_price = $unitPrice;
                $cartItem->save();
            });
        }
        return $cart->load(['items' => function ($q) {
            $q->with(['product', 'variant', 'vendor']);
        }]);
    }


    public function getMyCartGroupedByVendor(): array
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $cart = $this->model->query()
            ->with(['items' => function ($q) {
                $q->with(['product', 'variant', 'vendor', 'vendorUser']);
            }])
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$cart) {
            return ['cart' => []];
        }

        $grouped = $cart->items->groupBy('vendor_id');
        $response = [];

        /**
         * @var  int $vendorId
         * @var Collection $items
         */
        foreach ($grouped as $vendorId => $items) {
            // Get active discounts for this vendor once

            $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
            $discountArray = $this->getDiscountWithType($items, $vendorId);
            $discount = $discountArray['discount'] ?? 0;
            $type = $discountArray['type'] ?? null;
            $discountPercent = round(($discount / $subtotal) * 100, 2) ;
            //            $delivery = $this->calcDeliveryFees($buyerVendor, $vendorId);
            $delivery = 0.0;
            $total = max(0, $subtotal - $discount) + $delivery;

            $response[] = [
                // 'vendor_id' => $vendorId,
                'vendor_name' => optional($items->first()->vendor)->store_name,
                'summary' => [
                    'subtotal' => (float)$subtotal,
                    'discount' => $discount,
                    'type' => $type,
                    'delivery' => $delivery,
                    'total' => $total,
                ],
                'items' => $items->map(function ($item) use ($discountPercent) {
                    $itemDiscountData = [
                        'amount' => $item->unit_price * $discountPercent,
                        'percentage' => $discountPercent,
                        ]  ;

                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? null,
                        'image' => $item->product->getFirstMediaUrl('images'),
                        'variant' => $item->variant->color ?? null,
                        'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $itemDiscountData['percentage'],
                        'discount_amount' => $itemDiscountData['amount'],
                        'price_after_discount' => $item->unit_price - $itemDiscountData['amount'],
                        'total_price' => $item->unit_price * $item->quantity,
                        'total_after_discount' => ($item->unit_price - $itemDiscountData['amount']) * $item->quantity,
                    ];
                }),
            ];
        }

        return ['cart' => $response];
    }
    public function getMyCartGroupedByVendorConsumer(): array
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();

        $cart = $this->model->query()
            ->with(['items' => function ($q) {
                $q->with(['product', 'variant.sizes', 'vendor', 'vendorUser']);
            }])
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$cart) {
            return ['summary' => [], 'vendors' => []];
        }

        $grouped = $cart->items->groupBy('vendor_id');

        $vendors = [];
        $totalSubtotal = 0;
        $totalDiscount = 0;
        $totalDelivery = 0;
        $totalAmount = 0;

        foreach ($grouped as $vendorId => $items) {
            // Get active discounts for this vendor once


            $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
            $discountArray = $this->getDiscountWithType($items, $vendorId);
            $discount = $discountArray['discount'] ?? 0;
            $type = $discountArray['type'] ?? null;
            $discountPercent = round(($discount / $subtotal) * 100, 2) ;
            $delivery = 0.0; // حسب حسابك
            $total = max(0, $subtotal - $discount) + $delivery;

            $totalSubtotal += $subtotal;
            $totalDiscount += $discount;
            $totalDelivery += $delivery;
            $totalAmount += $total;

            $vendors[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => optional($items->first()->vendor)->store_name,
                'items' => $items->map(function ($item) use ($discountPercent, $type) {
                    if($type == 'discount'){
                        $itemDiscountData = $this->calcItemDiscount($item, $item->vendor_id);
                    }else {
                        $itemDiscountData = [
                            'amount' => $item->unit_price * $discountPercent,
                            'percentage' => $discountPercent,
                        ];
                    }

                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? null,
                        'image' => $item->product->getFirstMediaUrl('images'),
                        'variant' => $item->variant->color ?? null,
                        'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $itemDiscountData['percentage'],
                        'discount_amount' => $itemDiscountData['amount'],
                        'discount_type' => $type,
                        'price_after_discount' => $item->unit_price - $itemDiscountData['amount'],
                        'total_price' => $item->unit_price * $item->quantity,
                        'total_after_discount' => ($item->unit_price - $itemDiscountData['amount']) * $item->quantity,
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
            'summary' => $summary,
            'vendors' => $vendors,
        ];
    }



    /**
     * Remove an item and restore stock accordingly.
     * @throws ValidationException
     */
    public function removeItem(int $cartItemId): void
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $cart = $this->model->query()
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
        if (!$cart) {
            return;
        }
        /** @var CartItem $item */
        $item = CartItem::query()
            ->with(['product'])
            ->where('cart_id', $cart->id)
            ->findOrFail($cartItemId);
        $item->delete();
    }

    public function removeAll($vendorId): void
    {
        /** @var User $user */
        $user = Auth::guard('consumer-api')->user();
        $cart = $this->model->query()
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();
        if (!$cart) {
            return;
        }
        $cart->items()->where('user_id', $vendorId)->delete();
    }


    /**
     * Calculate discount for a single cart item.
     *
     * @param CartItem $item Cart item to calculate discount for
     * @param Collection $activeDiscounts Collection of active discounts
     * @return array ['percentage' => float, 'amount' => float]
     */
    private function calcItemDiscount($item, $vendorId): array
    {
        $activeDiscounts = Discount::query()
            ->active()
            ->where('vendor_id', $vendorId)
            ->with('products')
            ->get();

        if ($activeDiscounts->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0];
        }

        // Find discounts applicable to this product
        $applicableDiscounts = $activeDiscounts->filter(function ($discount) use ($item) {
            return $discount->products->contains('id', $item->product_id);
        });

        if ($applicableDiscounts->isEmpty()) {
            return ['percentage' => 0.0, 'amount' => 0.0, 'discount' => null];
        }

        // Apply the highest discount percentage if multiple discounts apply
        $maxDiscountPercentage = $applicableDiscounts->max('percentage') ?? 0.0;

        // Calculate discount amount per unit
        $discountAmount = $item->unit_price * ($maxDiscountPercentage / 100);

        return [
            'percentage' => (float)$maxDiscountPercentage,
            'amount' => (float)$discountAmount,
            'discount' => $applicableDiscounts->firstWhere('percentage', $maxDiscountPercentage),
        ];
    }

    /**
     * Calculate discount amount from the discounts table for cart items.
     *
     * @param Collection $items Cart items to calculate discount for
     * @param int $vendorId Vendor ID
     * @return float Total discount amount
     */
    private function calcDiscountAmount(Collection $items, int $vendorId): float
    {
        $totalDiscount = 0.0;
        $activeDiscounts = Discount::query()
            ->active()
            ->where('vendor_id', $vendorId)
            ->with('products')
            ->get();

        if ($activeDiscounts->isEmpty()) {
            return $totalDiscount;
        }

        foreach ($items as $item) {
            $itemDiscountData = $this->calcItemDiscount($item, $vendorId);
            $totalDiscount += $itemDiscountData['amount'] * $item->quantity;
        }
        return $totalDiscount;
    }

    private function calcVoucherDiscount($items, $vendorId): float|int
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
        return $discount;
    }

    /**
     * @param $buyerVendor
     * @param $vendorId
     * @return float
     */
    private function calcDeliveryFees($buyerVendor, $vendorId): float
    {
        $delivery = 0.0;
        if ($buyerVendor) {
            /** @var DeliveryArea $deliveryArea */
            $deliveryArea = DeliveryArea::query()
                ->where('vendor_id', $vendorId)
                ->where('state_id', $buyerVendor->state_id)
                ->where('city_id', $buyerVendor->city_id)
                ->first();
            if ($deliveryArea) {
                $delivery = (float)$deliveryArea->price;
            }
        }
        return $delivery;
    }
    public function calculateOfferAmount(Collection $items, int $vendorId): float
    {

        $offers = Offer::query()
            ->where('vendor_id', $vendorId)
            ->where('start', '<=', now())
            ->where('end', '>=', now())
            ->get();

        $quantity = $items->sum('quantity');
        $originalAmount  = $items->sum(fn($i) => $i->unit_price * $i->quantity);

        foreach ($offers as $offer) {
            switch ($offer->type) {
                case 'quantity':
                    if ($quantity >= $offer->quantity) {
                        return $originalAmount * ($offer->discount / 100);
                    }
                    break;

                case 'purchase':
                    if ($originalAmount >= $offer->amount) {
                        return $originalAmount * ($offer->discount / 100);
                    }
                    break;

                case 'custom':
                    if ($quantity >= $offer->buy) {
                        // calculate free items based on the lowest unit price and quantity until 'get' is reached
                        $sortedItems = $items->sortBy('unit_price');
                        $freeItemsCount = 0;
                        $discountAmount = 0.0;
                        foreach ($sortedItems as $item) {
                            for ($i = 0; $i < $item->quantity; $i++) {
                                if ($freeItemsCount < $offer->get) {
                                    $discountAmount += $item->unit_price;
                                    $freeItemsCount++;
                                } else {
                                    break 2; // exit both loops
                                }
                            }
                        }
                        return $discountAmount;
                    }
                    break;
            }
        }
        return 0.0;
    }


    private function getDiscountWithType(Collection $items, int $vendorId): array
    {
        $discount = $this->calcDiscountAmount($items, $vendorId);
        if($discount > 0){
            return ['type' => 'discount', 'amount' => $discount];
        }
        $discount = $this->calcVoucherDiscount($items, $vendorId);
        if($discount > 0){
            return ['type' => 'voucher', 'amount' => $discount];
        }
        $discount = $this->calculateOfferAmount($items, $vendorId);
        if($discount > 0){
            return ['type' => 'offer', 'amount' => $discount];
        }
        return ['type' => null, 'amount' => 0.0];
    }
}
