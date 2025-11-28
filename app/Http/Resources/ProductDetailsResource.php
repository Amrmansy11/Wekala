<?php

namespace App\Http\Resources;

use App\Http\Resources\Home\ColorsResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;

class ProductDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        $variants = $this->resource->variants->unique()->values()->all();
        $user = Auth::user();
        $hidePrice = $user && !$user->is_active;

        // Calculate discount percentage
        $discountPercentage = $this->wholesale_price > 0
            ? round((($this->wholesale_price - $this->consumer_price) / $this->wholesale_price) * 100)
            : 0;

        // Sizes from the first variant as an example
        $sizes = $this->variants->isNotEmpty() ? $this->variants->first()->sizes->mapWithKeys(function ($size) {
            return [$size->size => [
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
        $positiveRatingCount = $this->reviews()->where('rating', '>', 2)->count();
        $negativeRatingCount = $this->reviews()->where('rating', '<=', 2)->count();
        // ✅ Related sliders
        $sliders = $this->sliders()->latest()->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type ?? 'b2c',
            'description' => $this->description,
            'consumer_price' => $hidePrice ? null : $this->consumer_price,
            'original_price' => $hidePrice ? null : $this->wholesale_price,
            'discount_percentage' => $hidePrice ? null : $discountPercentage,
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
            'reviews' => [
                'average_rating' => $this->reviews()->avg('rating') ?? 0,
                'review_count' => $this->reviews()->count(),
                'with_images_videos' => $this->reviews()->where('has_images_or_videos', true)->count(),
                'repeat_customers' => $this->reviews()->where('is_repeat_customer', true)->count(),
                'positive_count' => $positiveRatingCount,
                'negative_count' => $negativeRatingCount,
            ],
            'is_best_seller' => $this->isBestSeller(),
            'sliders' => SliderResource::collection($sliders),
            'is_wishlist' => $this->resource->is_fav,

        ];
    }
}
