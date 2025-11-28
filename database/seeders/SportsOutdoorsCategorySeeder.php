<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class SportsOutdoorsCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Main Category
        $sportsOutdoors = Category::query()->create([
            'name'          => ['en' => 'Sports & Outdoors', 'ar' => 'الرياضة والهواء الطلق'],
            'parent_id'     => null,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);
        $brandsSportsOutdoorsMainCategory = [
            ['en' => 'Nike', 'ar' => 'نايكي', 'logo' => 'brands/Sports/nike.png'],
            ['en' => 'Adidas', 'ar' => 'أديداس', 'logo' => 'brands/Sports/adidas.png'],
            ['en' => 'Puma', 'ar' => 'بوما', 'logo' => 'brands/Sports/puma.png'],
            ['en' => 'New Balance', 'ar' => 'نيو بالانس', 'logo' => 'brands/Sports/new_balance.png'],
        ];
        foreach ($brandsSportsOutdoorsMainCategory as $item) {
            $brand = Brand::query()->create([
                'name'          => $item,
                'category_id'   => $sportsOutdoors->id,
                'is_active'     => 1,
                'creatable_type' => 'App\Models\Admin',
                'creatable_id'   => 1
            ]);
            $brand->clearMediaCollection('brand_logo');
            $brand->addMedia(public_path($item['logo']))
                ->usingName($item['en'])
                ->preservingOriginal()
                ->toMediaCollection('brand_logo');
        }
        // Subcategory: Sports
        $sports = Category::query()->create([
            'name'          => ['en' => 'Sports', 'ar' => 'الرياضة'],
            'parent_id'     => $sportsOutdoors->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $sportsSub = [
            ['en' => 'Basketball', 'ar' => 'كرة السلة'],
            ['en' => 'Soccer', 'ar' => 'كرة القدم'],
            ['en' => 'Baseball', 'ar' => 'البيسبول'],
            ['en' => 'Running', 'ar' => 'الجري'],
            ['en' => 'Cycling', 'ar' => 'ركوب الدراجات'],
            ['en' => 'Swimming', 'ar' => 'السباحة'],
            ['en' => 'Skiing', 'ar' => 'التزلج على الجليد'],
            ['en' => 'Snowboarding', 'ar' => 'التزلج على الثلج'],
            ['en' => 'Ice Skating', 'ar' => 'التزلج على الجليد'],
        ];
        foreach ($sportsSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $sports->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => null,
            ]);
        }



        // Subcategory: Outdoor Recreation
        $outdoor = Category::query()->create([
            'name'          => ['en' => 'Outdoor Recreation', 'ar' => 'الأنشطة الخارجية'],
            'parent_id'     => $sportsOutdoors->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $outdoorSub = [
            ['en' => 'Tents', 'ar' => 'خيام'],
            ['en' => 'Backpacks', 'ar' => 'حقائب ظهر'],
            ['en' => 'Sleeping Bags', 'ar' => 'أكياس نوم'],
            ['en' => 'Kayaking', 'ar' => 'التجديف'],
            ['en' => 'Paddleboarding', 'ar' => 'التجديف وقوفاً'],
            ['en' => 'Fishing', 'ar' => 'الصيد'],
            ['en' => 'Ropes', 'ar' => 'حبال'],
            ['en' => 'Harnesses', 'ar' => 'أحزمة أمان'],
            ['en' => 'Helmets', 'ar' => 'خوذات'],
        ];

        foreach ($outdoorSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $outdoor->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => null,
            ]);
        }

        // Subcategory: Fitness
        $fitness = Category::query()->create([
            'name'          => ['en' => 'Fitness', 'ar' => 'اللياقة البدنية'],
            'parent_id'     => $sportsOutdoors->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $fitnessSub = [
            ['en' => 'Dumbbells', 'ar' => 'دمبلز'],
            ['en' => 'Treadmills', 'ar' => 'أجهزة المشي'],
            ['en' => 'Yoga Mats', 'ar' => 'حصائر اليوغا'],
            ['en' => 'Tops', 'ar' => 'أعلى الملابس الرياضية'],
            ['en' => 'Bottoms', 'ar' => 'أسفل الملابس الرياضية'],
            ['en' => 'Shoes', 'ar' => 'أحذية رياضية'],
            ['en' => 'Protein', 'ar' => 'بروتين'],
            ['en' => 'Pre-Workout', 'ar' => 'مكملات قبل التمرين'],
            ['en' => 'Vitamins', 'ar' => 'فيتامينات'],
        ];

        foreach ($fitnessSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $fitness->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => null,
            ]);
        }
    }
}
