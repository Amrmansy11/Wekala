<?php

namespace App\Http\Requests\Consumer\Api\Cart;

use App\Http\Requests\ResponseShape;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;

/**
 * @property integer $product_id
 * @property array $product_variant_id
 */
class AddToCartRequest extends ResponseShape
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'product_variant_id' => ['required_without:quantity', 'array'],
            'product_variant_id.*.id' => [
                'required_with:product_variant_id',
                'integer',
                Rule::exists('product_variants', 'id')
                    ->where('product_id', $this->input('product_id')),
            ],
            'product_variant_id.*.size_id' => [
                'required_with:product_variant_id',
                'integer'
            ],
            'product_variant_id.*.quantity' => ['required_with:product_variant_id', 'integer', 'min:1'],
            'quantity' => ['required_without:product_variant_id', 'integer', 'min:1'],
        ];
    }

    /**
     * @param $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var Product $product */
            $product = Product::query()->find($this->input('product_id'));
            $variantPayload = collect($this->input('product_variant_id', []));
            if ($product->variants()->count()) {
                $variantIds = $variantPayload->pluck('id')
                    ->filter()
                    ->unique();
                if ($variantIds->count()) {
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
                            $validator->errors()->add('product_variant_id', __('validation.custom.product.not_found'));
                            continue;
                        }
                        $sizeId = $variant['size_id'] ?? null;
                        $validSizeIds = $productVariant->sizes
                            ->pluck('pivot.product_size_id')
                            ->map(fn($id) => (int)$id)
                            ->all();
                        if (!$sizeId || !in_array((int)$sizeId, $validSizeIds, true)) {
                            $validator->errors()->add('product_variant_id', __('validation.custom.product.invalid_size'));
                            continue;
                        }
                        $requestedQuantity = (int)($variant['quantity'] ?? 0);
                        $availableQuantity = $productVariant->sizes()
                            ->where('product_size_id', $sizeId)
                            ->first()?->pivot->total_quantity ?? 0;
                        if ($availableQuantity < $requestedQuantity) {
                            $validator->errors()->add('product_variant_id', __('validation.custom.product.quantity', [
                                'color' => $productVariant->color,
                                'stock' => $availableQuantity
                            ]));
                        }
                    }
                } else {
                    $validator->errors()->add('product_variant_id', __('validation.required', ['attribute' => 'product_variant_id']));
                }
            } else {
                $availableStock = $product->stock_b2c ?? 0;
                if ($availableStock < (int)$this->input('quantity')) {
                    $validator->errors()->add('quantity', __('validation.custom.product.stock', ['stock' => $availableStock]));
                }
                if ($this->input('product_variant_id')) {
                    $validator->errors()->add('product_variant_id', __('validation.custom.product.additional_variant'));
                }
            }
        });
    }
}
