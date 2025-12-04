<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeTemplateResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'template_name' => $this->resource->template_name,
            'chest' => $this->resource->chest,
            'chest_pattern' => $this->resource->chest_pattern,
            'product_length' => $this->resource->product_length,
            'length_pattern' => $this->resource->length_pattern,
            'weight_from' => $this->resource->weight_from,
            'weight_from_pattern' => $this->resource->weight_from_pattern,
            'weight_to' => $this->resource->weight_to,
            'weight_to_pattern' => $this->resource->weight_to_pattern,
            'type' => $this->resource->type,
        ];
    }
}
