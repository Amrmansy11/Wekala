<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class MenCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Main Category
        $mens = Category::query()->create([
            'name'          => ['en' => 'Men\'s Clothing', 'ar' => 'ملابس رجالية'],
            'parent_id'     => null,
            'is_active'     => 1,
            'size_required' => 1,
            'size'          => null,
        ]);

        $brandsMensMainCategory = [
            ['en' => 'Gucci', 'ar' => 'غوتشي', 'logo' => 'brands/Mens/Gucci.png'],
            ['en' => 'Prada', 'ar' => 'برادا', 'logo' => 'brands/Mens/Prada.png'],
            ['en' => 'Ralph Laure', 'ar' => 'رالف لور', 'logo' => 'brands/Mens/RalphLaure.png'],
            ['en' => 'Hugo Boss', 'ar' => 'هيوغو بوس', 'logo' => 'brands/Mens/HugoBoss.png'],
            ['en' => 'GANT', 'ar' => 'غانت', 'logo' => 'brands/Mens/GANT.png'],
            ['en' => 'Madewell', 'ar' => 'ماديويل', 'logo' => 'brands/Mens/Madewell.png'],
            ['en' => 'H&M', 'ar' => 'إتش آند إم', 'logo' => 'brands/Mens/HM.png'],
            ['en' => 'Zara', 'ar' => 'زارا', 'logo' => 'brands/Mens/Zara.png'],
            ['en' => 'Spier & Mackay', 'ar' => 'سبير أند ماكاي', 'logo' => 'brands/Mens/SpierMackay.png'],
            ['en' => 'Charles Tyrwhit', 'ar' => 'تشارلز تيرويت', 'logo' => 'brands/Mens/CharlesTyrwhit.jpg'],
            ['en' => 'Nike', 'ar' => 'نايك', 'logo' => 'brands/Mens/Nike.png'],
            ['en' => 'Adidas', 'ar' => 'أديداس', 'logo' => 'brands/Mens/Adidas.png'],

        ];

        foreach ($brandsMensMainCategory as $item) {
            $brand = Brand::query()->create([
                'name'          => $item,
                'category_id'   => $mens->id,
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

        // Subcategory: Activewear
        $activewear = Category::query()->create([
            'name'          => ['en' => 'Activewear', 'ar' => 'ملابس رياضية'],
            'parent_id'     => $mens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $activewearSub = [
            ['en' => 'Shorts', 'ar' => 'شورتات', 'size' => 'pants', 'image' => 'category/Mens/Shorts.jpg'],
            ['en' => 'T-shirts', 'ar' => 'تيشيرتات', 'size' => 'tshirt', 'image' => 'category/Mens/Tshirts.jpg'],
            ['en' => 'Leggings', 'ar' => 'ليجنز', 'size' => 'pants', 'image' => 'category/Mens/Leggings.jpg'],
            ['en' => 'Tank Tops', 'ar' => 'تيشرتات بلا أكمام', 'size' => 'tshirt', 'image' => 'category/Mens/TankTops.jpg'],
            ['en' => 'Hoodies', 'ar' => 'هوديز', 'size' => 'tshirt', 'image' => 'category/Mens/Hoodies.jpg'],
            ['en' => 'Joggers', 'ar' => 'بنطلونات رياضية', 'size' => 'pants', 'image' => 'category/Mens/Leggings.jpg'],
            ['en' => 'Sweatshirts', 'ar' => 'سويت شيرتات', 'size' => 'tshirt', 'image' => 'category/Mens/Tshirts.jpg'],
        ];

        foreach ($activewearSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $activewear->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
            $category->clearMediaCollection('category_image');
            $category->addMedia(public_path($item['image']))
                ->usingName($item['en'])
                ->preservingOriginal()
                ->toMediaCollection('category_image');
        }

        // Subcategory: Formal Wear
        $formal = Category::query()->create([
            'name'          => ['en' => 'Formal Wear', 'ar' => 'ملابس رسمية'],
            'parent_id'     => $mens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $formalSub = [
            ['en' => 'Tuxedos', 'ar' => 'بدل رسمية', 'size' => 'tshirt', 'image' => 'category/Mens/Tuxedos.jpg'],
            ['en' => 'Suit Jackets', 'ar' => 'جاكيتات بدلة', 'size' => 'tshirt', 'image' => 'category/Mens/Tuxedos.jpg'],
            ['en' => 'Twill', 'ar' => 'تويل', 'size' => 'pants', 'image' => 'category/Mens/Twill.jpg'],
            ['en' => 'Poplin', 'ar' => 'بوبلين', 'size' => 'pants', 'image' => 'category/Mens/Twill.jpg'],
            ['en' => 'Oxford', 'ar' => 'أكسفورد', 'size' => 'pants', 'image' => 'category/Mens/Oxford.jpg'],
            ['en' => 'Formal Trousers', 'ar' => 'بنطلونات رسمية', 'size' => 'pants', 'image' => 'category/Mens/FormalTrousers.jpg'],
        ];

        foreach ($formalSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $formal->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
            $category->clearMediaCollection('category_image');
            $category->addMedia(public_path($item['image']))
                ->usingName($item['en'])
                ->preservingOriginal()
                ->toMediaCollection('category_image');
        }

        // Subcategory: Loungewear
        $loungewear = Category::query()->create([
            'name'          => ['en' => 'Loungewear', 'ar' => 'ملابس منزلية'],
            'parent_id'     => $mens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $loungewearSub = [
            ['en' => 'Pajama Sets', 'ar' => 'بيجامات', 'size' => 'tshirt', 'image' => 'category/Mens/PajamaSets.jpg'],
            ['en' => 'Robes', 'ar' => 'أردية', 'size' => 'tshirt', 'image' => 'category/Mens/Robes.jpg'],
            ['en' => 'Sweatpants', 'ar' => 'بنطال رياضي', 'size' => 'pants', 'image' => 'category/Mens/Sweatpants.jpg'],
            ['en' => 'Hoodies', 'ar' => 'هوديز', 'size' => 'tshirt', 'image' => 'category/Mens/Hoodies.jpg'],
        ];

        foreach ($loungewearSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $loungewear->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
            $category->clearMediaCollection('category_image');
            $category->addMedia(public_path($item['image']))
                ->usingName($item['en'])
                ->preservingOriginal()
                ->toMediaCollection('category_image');
        }

        // Subcategory: Swimwear
        $swimwear = Category::query()->create([
            'name'          => ['en' => 'Swimwear', 'ar' => 'ملابس سباحة'],
            'parent_id'     => $mens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $swimwearSub = [
            ['en' => 'Swim Trunks', 'ar' => 'شورت سباحة', 'size' => 'pants'],
            ['en' => 'Boardshorts', 'ar' => 'شورتات بورد', 'size' => 'pants'],
        ];

        foreach ($swimwearSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $swimwear->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
        }

        // Subcategory: Seasonal
        $seasonal = Category::query()->create([
            'name'          => ['en' => 'Seasonal', 'ar' => 'موسمية'],
            'parent_id'     => $mens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);


        $seasonalSub = [
            ['en' => 'Sweaters', 'ar' => 'كنزات', 'size' => 'tshirt', 'image' => 'category/Mens/Sweaters.jpg'],
            ['en' => 'Coats', 'ar' => 'معاطف', 'size' => 'tshirt', 'image' => 'category/Mens/Coats.jpg'],
            ['en' => 'Scarves', 'ar' => 'أوشحة', 'size' => 'tshirt', 'image' => 'category/Mens/Scarves.jpg'],
            ['en' => 'Shorts', 'ar' => 'شورتات', 'size' => 'pants', 'image' => 'category/Mens/Scarves.jpg'],
            ['en' => 'Tanks', 'ar' => 'تيشيرتات بلا أكمام', 'size' => 'tshirt', 'image' => 'category/Mens/Tanks.jpg'],
            ['en' => 'Sandals', 'ar' => 'صنادل', 'size' => 'pants', 'image' => 'category/Mens/Scarves.jpg'],
        ];

        foreach ($seasonalSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $seasonal->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
            $category->clearMediaCollection('category_image');
            $category->addMedia(public_path($item['image']))
                ->usingName($item['en'])
                ->preservingOriginal()
                ->toMediaCollection('category_image');
        }
    }
}
