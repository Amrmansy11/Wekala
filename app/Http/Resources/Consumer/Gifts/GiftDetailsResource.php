<?php

namespace App\Http\Resources\Consumer\Gifts;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
