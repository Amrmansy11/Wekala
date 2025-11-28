<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SizeChartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'size' => $this->size,
            'waist' => $this->waist,
            'length' => $this->length,
            'chest' => $this->chest,
            'weight_range' => $this->weight_range,
            'bundles' => $this->bundles,
        ];
    }
}
