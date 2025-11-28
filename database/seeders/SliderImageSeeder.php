<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

class SliderImageSeeder extends Seeder
{
    public function run(): void
    {
        // الصور بالترتيب
        $images = [
            'banners/banner1.png',
            'banners/banner2.png',
            'banners/banner3.png',
            'banners/banner4.png',
        ];

        // هياخد كل سلايدر بالترتيب
        Slider::all()->each(function ($slider, $index) use ($images) {
            /** @var Slider $slider */
            $slider->clearMediaCollection('images');

            if (isset($images[$index])) {
                $slider
                    ->addMedia(public_path($images[$index]))
                    ->preservingOriginal()
                    ->toMediaCollection('images', 'public');
            }
        });
    }
}
