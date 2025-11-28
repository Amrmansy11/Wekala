<?php

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Slider::query()->truncate();
        DB::table('slider_product')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $sliders = [
            [
                'name'        => 'موضة عصرية وأسلوب حياة مبهر - WOW',
                'description' => 'اكتشف مجموعة مميزة من الأزياء العصرية التي تجمع بين الأناقة والراحة.',
                'image'       => 'banners/banner1.png',
                'type'        => 'consumer',
            ],
            [
                'name'        => 'خصم حتى 20% على مستحضرات التجميل – عرض لفترة محدودة',
                'description' => 'استمتعي بأجمل مستحضرات التجميل مع خصم يصل إلى 20%.',
                'image'       => 'banners/banner2.png',
                'type'        => 'consumer',
            ],
            [
                'name'        => 'عروض الأزياء الرجالية – خصومات مميزة على أحدث صيحات الموضة',
                'description' => 'تسوق الآن أحدث صيحات الموضة الرجالية مع عروض وخصومات خاصة.',
                'image'       => 'banners/banner3.png',
                'type'        => 'consumer',
            ],
            [
                'name'        => 'الأكثر مبيعاً هذا الموسم – تسوق الآن',
                'description' => 'استمتع بأفضل العروض على المنتجات الأكثر مبيعاً هذا الموسم.',
                'image'       => 'banners/banner4.png',
                'type'        => 'consumer',
            ],
            [
                'name'        => 'موضة عصرية وأسلوب حياة مبهر - WOW',
                'description' => 'اكتشف مجموعة مميزة من الأزياء العصرية التي تجمع بين الأناقة والراحة.',
                'image'       => 'banners/banner1.png',
                'type'        => 'seller',
            ],
            [
                'name'        => 'خصم حتى 20% على مستحضرات التجميل – عرض لفترة محدودة',
                'description' => 'استمتعي بأجمل مستحضرات التجميل مع خصم يصل إلى 20%.',
                'image'       => 'banners/banner2.png',
                'type'        => 'seller',
            ],
            [
                'name'        => 'عروض الأزياء الرجالية – خصومات مميزة على أحدث صيحات الموضة',
                'description' => 'تسوق الآن أحدث صيحات الموضة الرجالية مع عروض وخصومات خاصة.',
                'image'       => 'banners/banner3.png',
                'type'        => 'seller',
            ],
            [
                'name'        => 'الأكثر مبيعاً هذا الموسم – تسوق الآن',
                'description' => 'استمتع بأفضل العروض على المنتجات الأكثر مبيعاً هذا الموسم.',
                'image'       => 'banners/banner4.png',
                'type'        => 'seller',
            ],
        ];

        foreach ($sliders as $data) {
            $slider = Slider::query()->create([
                'name'        => $data['name'],
                'description' => $data['description'],
                'is_active'   => 1,
                'type'        => $data['type'],
            ]);

            $products = Product::query()->inRandomOrder()->limit(10)->pluck('id');
            if ($products->count()) {
                $slider->products()->attach($products);
            }

            // add banner image
            if (file_exists(public_path($data['image']))) {
                $slider
                    ->addMedia(public_path($data['image']))
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            } else {
                dump("Image not found: " . $data['image']);
            }
        }
    }
}
