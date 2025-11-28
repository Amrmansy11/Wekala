<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ElwekalaCollectionShowResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'product' => new ElwekalaCollectionProductResource($this->whenLoaded('product')),
        ];
    }
}
