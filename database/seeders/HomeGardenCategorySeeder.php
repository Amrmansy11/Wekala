<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class HomeGardenCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Main Category
        $home = Category::query()->create([
            'name'          => ['en' => 'Home & Garden', 'ar' => 'المنزل والحديقة'],
            'parent_id'     => null,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $brandsHomeGardenMainCategory = [
            ['en' => 'IKEA', 'ar' => 'إيكيا', 'logo' => 'brands/HomeGarden/IKEA.jpg'],
            ['en' => 'West Elm', 'ar' => 'ويست إلم', 'logo' => 'brands/HomeGarden/WestElm.png'],
            ['en' => 'Wayfair', 'ar' => 'وايفير', 'logo' => 'brands/HomeGarden/Wayfair.png'],
            ['en' => 'Pottery Barn', 'ar' => 'بوتري بارن', 'logo' => 'brands/HomeGarden/PotteryBarn.png'],
            ['en' => 'DWR', 'ar' => 'دي دبليو آر', 'logo' => 'brands/HomeGarden/DWR.jpg'],
        ];

        foreach ($brandsHomeGardenMainCategory as $item) {
            $brand = Brand::query()->create([
                'name'          => $item,
                'category_id'   => $home->id,
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
        // Subcategory: Furniture
        $furniture = Category::query()->create([
            'name'          => ['en' => 'Furniture', 'ar' => 'الأثاث'],
            'parent_id'     => $home->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $furnitureSub = [
            ['en' => 'Sofas', 'ar' => 'أرائك'],
            ['en' => 'Coffee Tables', 'ar' => 'طاولات قهوة'],
            ['en' => 'TV Stands', 'ar' => 'حاملات تلفزيون'],
            ['en' => 'Beds', 'ar' => 'أسِرّة'],
            ['en' => 'Dressers', 'ar' => 'خزائن'],
            ['en' => 'Nightstands', 'ar' => 'طاولات جانبية'],
            ['en' => 'Patio Sets', 'ar' => 'مجموعات فناء'],
            ['en' => 'Loungers', 'ar' => 'مقاعد استرخاء'],
            ['en' => 'Umbrellas', 'ar' => 'مظلات'],
        ];

        foreach ($furnitureSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $furniture->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }

        // Subcategory: Tools & Hardware
        $tools = Category::query()->create([
            'name'          => ['en' => 'Tools & Hardware', 'ar' => 'الأدوات والمعدات'],
            'parent_id'     => $home->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $toolsSub = [
            ['en' => 'Drills', 'ar' => 'مثاقب'],
            ['en' => 'Saws', 'ar' => 'مناشير'],
            ['en' => 'Sanders', 'ar' => 'أجهزة صنفرة'],
            ['en' => 'Wrenches', 'ar' => 'مفاتيح ربط'],
            ['en' => 'Screwdrivers', 'ar' => 'مفكات براغي'],
            ['en' => 'Pliers', 'ar' => 'كماشات'],
            ['en' => 'Shovels', 'ar' => 'مجارف'],
            ['en' => 'Rakes', 'ar' => 'مناجل/مشط حديقة'],
            ['en' => 'Pruners', 'ar' => 'مقصات تقليم'],
        ];

        foreach ($toolsSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $tools->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }

        // Subcategory: Decor
        $decor = Category::query()->create([
            'name'          => ['en' => 'Decor', 'ar' => 'الديكور'],
            'parent_id'     => $home->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $decorSub = [
            ['en' => 'Mirrors', 'ar' => 'مرايا'],
            ['en' => 'Paintings', 'ar' => 'لوحات'],
            ['en' => 'Frames', 'ar' => 'إطارات'],
            ['en' => 'Lamps', 'ar' => 'مصابيح'],
            ['en' => 'Chandeliers', 'ar' => 'ثريات'],
            ['en' => 'String Lights', 'ar' => 'أضواء خيطية'],
            ['en' => 'Rugs', 'ar' => 'سجاد'],
            ['en' => 'Curtains', 'ar' => 'ستائر'],
            ['en' => 'Pillows', 'ar' => 'وسائد'],
        ];

        foreach ($decorSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $decor->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }
    }
}
