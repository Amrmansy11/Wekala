<?php

namespace App\Http\Resources\Consumer\Wishlist;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Consumer\Home\ProductResource;

class WishlistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'product' => new ProductResource($this->product),
        ];
    }
}
