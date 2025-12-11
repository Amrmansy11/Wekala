<?php

namespace App\Repositories\Vendor;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\ProductVariant;
use App\Models\VendorUser;
use App\Models\Voucher;
use App\Models\DeliveryArea;
use App\Models\Vendor;
use App\Repositories\BaseRepository;
use Exception;
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

        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();

        $cart = $this->model->query()
            ->firstOrCreate([
                'vendor_id' => $vendorUser->vendor_id,
                'status' => 'open',
            ]);

        /** @var Product $product */
        $product = $this->productRepository->find($data['product_id']);
        $unitPrice = $product->wholesale_price;
        $product->refresh();

        if ($product->variants()->count() && count($data['product_variant_id'])) {
            foreach ($data['product_variant_id'] as $variant) {
                /** @var ProductVariant $productVariant */
                $productVariant = ProductVariant::query()->find($variant['id']);
                if (!$productVariant) {
                    throw ValidationException::withMessages([
                        'product_variant_id' => [__('validation.custom.product.not_found')],
                    ]);
                }
                $availableQuantity = $productVariant->quantity_b2b ?? 0;
                if ($availableQuantity < $variant['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => [__(
                            'validation.custom.product.quantity',
                            ['color' => $productVariant->color, 'stock' => $availableQuantity]
                        )],
                    ]);
                }
            }

            DB::transaction(function () use ($data, $unitPrice, $product, $cart, $vendorUser) {
                foreach ($data['product_variant_id'] as $variant) {
                    $cartItem = CartItem::query()->firstOrNew([
                        'cart_id' => $cart->id,
                        'vendor_id' => $product->vendor_id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant['id'],
                    ]);

                    $cartItem->vendor_user_id = $vendorUser->id;
                    $cartItem->quantity = $variant['quantity'];
                    $cartItem->unit_price = $unitPrice;
                    $cartItem->save();
                }
            });
        } else {
            DB::transaction(function () use ($data, $unitPrice, $product, $cart, $vendorUser) {
                $cartItem = CartItem::query()->firstOrNew([
                    'cart_id' => $cart->id,
                    'vendor_id' => $product->vendor_id,
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                ]);
                $cartItem->vendor_user_id = $vendorUser->id;
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
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();
        $cart = $this->model->query()
            ->with(['items' => function ($q) {
                $q->with(['product', 'variant', 'vendor', 'vendorUser']);
            }])
            ->where('vendor_id', $vendorUser->vendor_id)
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
            $subtotal = $items->sum(fn($i) => $i->unit_price * $i->quantity);
//            $discount = $this->calcVoucherDiscount($items, $vendorId);
//            $delivery = $this->calcDeliveryFees($buyerVendor, $vendorId);
            $discount = 0.0;
            $delivery = 0.0;
            $total = max(0, $subtotal - $discount) + $delivery;

            $response[] = [
                'vendor_id' => $vendorId,
                'vendor_name' => optional($items->first()->vendor)->store_name,
                'summary' => [
                    'subtotal' => (float)$subtotal,
                    'discount' => $discount,
                    'delivery' => $delivery,
                    'total' => $total,
                ],
                'items' => $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_name' => $item->product->name ?? null,
                        'image' => $item->product->getFirstMediaUrl('images'),
                        'variant' => $item->variant->color ?? null,
                        'size' => $item->variant ? $item->variant->sizes->pluck('size')->toArray() : [],
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->unit_price * $item->quantity,
                        'is_wishlist' => $item->product->is_fav,
                    ];
                }),
            ];
        }

        return ['cart' => $response];
    }


    /**
     * Remove an item and restore stock accordingly.
     * @throws ValidationException
     */
    public function removeItem(int $cartItemId): void
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();
        $cart = $this->model->query()
            ->where('vendor_id', $vendorUser->vendor_id)
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

        $product = $item->product;

        if ($product->variants()->count() && $product->min_color) {
            $colorsCount = $cart->items()
                ->where('product_id', $product->id)
                ->distinct('product_variant_id')
                ->count('product_variant_id');
            if ($colorsCount <= $product->min_color) {
                throw ValidationException::withMessages([
                    'color' => __('validation.custom.product.min_color', ['min_color' => $product->min_color]),
                ]);
            }
        }
        $item->delete();
    }

    public function removeAll($vendorId): void
    {
        /** @var VendorUser $vendorUser */
        $vendorUser = Auth::guard('vendor-api')->user();
        $cart = $this->model->query()
            ->where('vendor_id', $vendorUser->vendor_id)
            ->where('status', 'open')
            ->first();
        if (!$cart) {
            return;
        }
        $cart->items()->where('vendor_id', $vendorId)->delete();
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

    private function calcDeliveryFees($buyerVendor, $vendorId): float
    {
        $delivery = 0.0;
        if ($buyerVendor) {
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
}
