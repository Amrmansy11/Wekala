<?php

namespace App\Http\Resources\Consumer\Voucher;

use Illuminate\Http\Resources\Json\JsonResource;

class VoucherDetailsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
