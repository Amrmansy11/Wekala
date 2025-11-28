<?php

namespace App\Http\Resources;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Slider */
class HomeSliderResource extends JsonResource
{
    public function toArray($request): array
    {
        //        $images = [
        //            'banners/banner1.png',
        //            'banners/banner2.png',
        //            'banners/banner3.png',
        //            'banners/banner4.png',
        //        ];


        //        $image = $images[$this->resource->id % count($images)];
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'images' => $this->getFirstMediaUrl('images'),
            //            'images' => asset($image),
        ];
    }
}
