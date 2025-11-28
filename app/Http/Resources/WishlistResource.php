<?php

namespace App\Http\Resources;

use App\Http\Resources\Home\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
