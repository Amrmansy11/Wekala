<?php

namespace App\Http\Resources;

use App\Models\Brand;
use App\Models\Color;
use App\Models\Product;
use App\Models\Category;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->resource->id,
            'name' => $this->getName(),
        ];
        if ($this->resource instanceof Role) {
            $data['permissions'] = $this->resource->permissions->pluck('name')->toArray();
        }
        if ($this->resource instanceof Category) {
            $data['image'] = $this->resource->getFirstMediaUrl('category_image') ?? null;
            $data['size'] = $this->resource->size;
            $data['size_required'] = $this->resource->size_required;
        }
        if ($this->resource instanceof Brand) {
            $data['logo'] = $this->resource->getFirstMediaUrl('brand_logo') ?? null;
        }
        if ($this->resource instanceof Color) {
            $data['hexcolor'] = $this->resource->hex_code;
        }
        if ($this->resource instanceof Product) {
            $data['consumer_price'] = $this->resource->consumer_price;
            $data['wholesale_price'] = $this->resource->wholesale_price;
            $data['description'] = $this->resource->description;
            $data['image'] = $this->resource->getFirstMediaUrl('images') ?? null;
        }
        return $data;
    }

    private function getName(): string
    {
        return $this->resource->name;
    }
}
