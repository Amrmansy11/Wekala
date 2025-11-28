<?php

namespace App\Http\Resources\Consumer\Discount;

use Illuminate\Http\Resources\Json\JsonResource;

class DiscountDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'percentage' => $this->percentage,
        ];
    }
}

