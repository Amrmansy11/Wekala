<?php

namespace App\Http\Resources\Consumer\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
            'number_of_use' => $this->number_of_use,
            'number_of_use_per_person' => $this->number_of_use_per_person,
            'for_all' => $this->for_all,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'is_active' => $this->number_of_use > 0 && now()->between($this->start_date, $this->end_date),
            'creatable' => $this->whenLoaded('creatable', function () {
                return [
                    'id' => $this->creatable->id,
                    'name' => $this->creatable->name ?? $this->creatable->email, // Adjust based on your creatable model
                    'type' => $this->creatable_type,
                ];
            }),
        ];
    }
}
