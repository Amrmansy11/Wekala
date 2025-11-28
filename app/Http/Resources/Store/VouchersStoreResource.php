<?php

namespace App\Http\Resources\Store;

use Illuminate\Http\Resources\Json\JsonResource;

class VouchersStoreResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'percentage' => $this->percentage,
            'amount' => $this->amount,
            'for_all' => $this->for_all,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
