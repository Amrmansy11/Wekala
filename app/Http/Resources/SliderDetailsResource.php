<?php

namespace App\Http\Resources;

use App\Models\Slider;
use Illuminate\Http\Request;
use App\Http\Resources\Home\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Slider */
class SliderDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'total_products' => count($this->resource->products)
//            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
