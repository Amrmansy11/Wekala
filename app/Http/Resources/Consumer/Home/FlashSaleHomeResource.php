<?php

namespace App\Http\Resources\Consumer\Home;

use Illuminate\Http\Resources\Json\JsonResource;

class FlashSaleHomeResource extends JsonResource
{
    public function toArray($request): array
    {
        // dd($this->resource);
        return [
            'id' => $this->resource->product->id,
            'title' => $this->resource->product->name,
            'consumer_price' => $this->resource->product->consumer_price,
            'image' => $this->resource->product->getFirstMediaUrl('images'),
        ];
    }
}
