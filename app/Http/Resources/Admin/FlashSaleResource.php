<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\FlashSaleProductResource;

class FlashSaleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => new FlashSaleProductResource($this->whenLoaded('product')),
        ];
    }
}
