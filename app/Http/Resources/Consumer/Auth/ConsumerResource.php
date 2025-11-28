<?php

namespace App\Http\Resources\Consumer\Auth;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsumerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'birthday' => $this->resource->birthday,
            'roles' => $this->resource->getRoleNames(),
            'permissions' => $this->resource->getAllPermissions()->pluck('name')
        ];
    }
}
