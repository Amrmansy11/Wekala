<?php

namespace App\Http\Resources\Consumer\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SizeChartResource;
use App\Http\Resources\Home\BrandResource;
use App\Http\Resources\SubCategoriesResource;
use App\Http\Resources\SubSubCategoriesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Products\ColorsResource;
use App\Http\Resources\Consumer\Products\SliderResource;
use App\Http\Resources\Consumer\Products\VendorResource;
use App\Http\Resources\Consumer\Products\ProductResource;
use App\Http\Resources\Consumer\Products\ReviewsResource;
use App\Http\Resources\Consumer\Products\VoucherResource;
use App\Http\Resources\Consumer\Products\CategoriesResource;

class ProductDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        $user = Auth::user();
        $hidePrice = $user && !$user->is_active;

        // Calculate discounted price
        $originalPrice = $this->wholesale_price ?? $this->consumer_price;
        $discountPercentage = $this->discount_percentage ?? 0;

        $discountedPrice = $originalPrice - ($originalPrice * ($discountPercentage / 100));

        // Calculate sold count from order items
        $soldCount = $this->sold_count ?? 0;

        // Format sold count (e.g., 48000 -> "48K")
        $soldCountFormatted = $soldCount >= 1000
            ? number_format($soldCount / 1000, 1) . 'K'
            : (string) $soldCount;

        // Get average rating
        $averageRating = $this->whenLoaded('reviews', function () {
            return round($this->reviews->avg('rating') ?? 0, 1);
        }, 0);
        // Sizes from the first variant as an example
        $sizes = $this->variants->isNotEmpty() ? $this->variants->first()->sizes->mapWithKeys(function ($size) {
            return [$size->size => [
                'id' => $size->id,
                'quantity' => $size->pivot->quantity,
                'pieces_per_bag' => $size->pieces_per_bag
            ]];
        })->all() : [];

        // Gallery of product images
        $gallery = $this->getMedia('images')->map(fn($media) => $media->getUrl())->all();

        // ✅ Matching styles (last 6 products in the same sub_sub_category)
        $matchingStyles = Product::query()
            ->where('sub_sub_category_id', $this->sub_sub_category_id)
            ->where('id', '!=', $this->id)
            ->latest()
            ->take(6)
            ->get();

        // ✅ Ratings breakdown
       
        // ✅ Related sliders
        $sliders = $this->sliders()->latest()->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type ?? 'b2c',
            'description' => $this->description,
            'current_price' => number_format($discountedPrice, 0) . ' EGP',
            'original_price' => number_format($originalPrice, 0) . ' LE',
            'discounted_price' => $discountedPrice,
            'discount_percentage' => $hidePrice ? null : $discountPercentage,
            'original_price_value' => $originalPrice,
            'rating' => $averageRating,
            'sold_count' => $soldCount,
            'sold_count_formatted' => $soldCountFormatted,
            'sold_display' => "(+{$soldCountFormatted} Sold)",


            'published_at' => $this->published_at,
            'stock' => $this->stock,
            'min_color' => $this->min_color,
            'views' => 5000,
            'favorites' => 25000,
            'sales' => 266000,
            'rating' => 4.8,
            'rating_count' => 563,
            'colors' => $variants ? ColorsResource::collection($variants) : [],
            'sizes' => $sizes,
            'material' => $this->material?->name,
            'brand' => $this->brand ? new BrandResource($this->brand) : null,
            'category' => $this->category ? new CategoriesResource($this->category) : null,
            'sub_category' => $this->subCategory ? new SubCategoriesResource($this->subCategory) : null,
            'sub_sub_category' => $this->subSubCategory ? new SubSubCategoriesResource($this->subSubCategory) : null,
            'size_chart' => SizeChartResource::collection($this->productMeasurement),
            'gallery' => $gallery,

            // ✅ Wrap with ProductResource
            'matching_styles' => ProductResource::collection($matchingStyles),
            'vendor' => new VendorResource($this->vendor),

            'vouchers' => VoucherResource::collection($this->vouchers()->where('for_all', true)->orWhereHas('products', function ($query) {
                $query->where('product_id', $this->id);
            })->where('start_date', '<=', now())->where('end_date', '>=', now())->where('number_of_use', '>', 0)->get()),
            'reviews' => ReviewsResource::collection($this->whenLoaded('reviews')),
            'is_best_seller' => $this->isBestSeller(),
            'sliders' => SliderResource::collection($sliders),
            'is_wishlist' => $this->resource->is_fav,
        ];
    }
}
