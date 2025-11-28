<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoriesResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'main_category' => $this->resource?->parent?->name,
            'name' => $this->resource->name,

        ];
    }
}
