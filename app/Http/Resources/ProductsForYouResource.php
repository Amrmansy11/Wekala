<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductsForYouResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['title'],
            'image' => asset($this->resource['image']),
            'stock' => $this->resource['stock'],
            'price' => auth()->check() && auth()->user()->is_active ? $this->resource['price'] : 'Price hidden',
            'colors' => $this->resource['color']

        ];
    }
}
