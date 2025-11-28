<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Helpers\AppHelper;
use Illuminate\Database\Seeder;

class HealthBeautyCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Main Category
        $healthBeauty = Category::query()->create([
            'name'          => ['en' => 'Health & Beauty', 'ar' => 'الصحة والجمال'],
            'parent_id'     => null,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $brandsHealthBeautyMainCategory = [
            ['en' => 'CeraVe', 'ar' => 'سيرافي', 'logo' => 'brands/HealthBeauty/CeraVe.png'],
            ['en' => 'Neutrogena', 'ar' => 'نيوتروجينا', 'logo' => 'brands/HealthBeauty/Neutrogena.png'],
            ['en' => 'Cetaphil', 'ar' => 'سيتافيل', 'logo' => 'brands/HealthBeauty/Cetaphil.png'],
            ['en' => 'Clinique', 'ar' => 'كلينيك', 'logo' => 'brands/HealthBeauty/Clinique.png'],
            ['en' => 'Ordinary', 'ar' => 'أورديناري', 'logo' => 'brands/HealthBeauty/Ordinary.png'],
            ['en' => 'Maybelline', 'ar' => 'ميبلين', 'logo' => 'brands/HealthBeauty/Maybelline.png'],
            ['en' => 'L\'Oréal Paris', 'ar' => 'لوريال باريس', 'logo' => 'brands/HealthBeauty/Oréal.png'],
            ['en' => 'e.l.f. Cosmetics', 'ar' => 'إي إل إف', 'logo' => 'brands/HealthBeauty/Cosmetics.png'],
        ];
        foreach ($brandsHealthBeautyMainCategory as $item) {
            $brand = Brand::query()->create([
                'name'          => $item,
                'category_id'   => $healthBeauty->id,
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
        // Subcategory: Skincare
        $skincare = Category::query()->create([
            'name'          => ['en' => 'Skincare', 'ar' => 'العناية بالبشرة'],
            'parent_id'     => $healthBeauty->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $skincareSub = [
            ['en' => 'Foaming', 'ar' => 'منظف رغوي'],
            ['en' => 'Cream', 'ar' => 'كريم'],
            ['en' => 'Gel', 'ar' => 'جل'],
            ['en' => 'Day Creams', 'ar' => 'كريمات نهارية'],
            ['en' => 'Night Creams', 'ar' => 'كريمات ليلية'],
            ['en' => 'Serums', 'ar' => 'سيرومات'],
            ['en' => 'Acne', 'ar' => 'حب الشباب'],
            ['en' => 'Anti-Aging', 'ar' => 'مضاد للشيخوخة'],
            ['en' => 'Dark Spot', 'ar' => 'بقع داكنة'],
        ];

        foreach ($skincareSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $skincare->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }

        // Subcategory: Haircare
        $haircare = Category::query()->create([
            'name'          => ['en' => 'Haircare', 'ar' => 'العناية بالشعر'],
            'parent_id'     => $healthBeauty->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $haircareSub = [
            ['en' => 'For Oily Hair', 'ar' => 'للشعر الدهني'],
            ['en' => 'For Dry Hair', 'ar' => 'للشعر الجاف'],
            ['en' => 'Color-Safe', 'ar' => 'مناسب للشعر المصبوغ'],
            ['en' => 'Gels', 'ar' => 'جل تصفيف'],
            ['en' => 'Sprays', 'ar' => 'بخاخات'],
            ['en' => 'Mousses', 'ar' => 'رغوة/موس'],
            ['en' => 'Hair Dryers', 'ar' => 'مجففات شعر'],
            ['en' => 'Straighteners', 'ar' => 'مكواة فرد الشعر'],
            ['en' => 'Brushes', 'ar' => 'فرش الشعر'],
        ];
        foreach ($haircareSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $haircare->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }

        // Subcategory: Makeup
        $makeup = Category::query()->create([
            'name'          => ['en' => 'Makeup', 'ar' => 'مستحضرات التجميل'],
            'parent_id'     => $healthBeauty->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $makeupSub = [
            ['en' => 'Foundation', 'ar' => 'فاونديشن'],
            ['en' => 'Concealer', 'ar' => 'كونسيلر'],
            ['en' => 'Blush', 'ar' => 'أحمر خدود'],
            ['en' => 'Eyeshadow', 'ar' => 'ظلال عيون'],
            ['en' => 'Mascara', 'ar' => 'ماسكارا'],
            ['en' => 'Eyeliner', 'ar' => 'كحل/آيلاينر'],
            ['en' => 'Lipstick', 'ar' => 'أحمر شفاه'],
            ['en' => 'Lip Gloss', 'ar' => 'ملمع شفاه'],
            ['en' => 'Lip Balm', 'ar' => 'بلسم شفاه'],
        ];

        foreach ($makeupSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $makeup->id,
                'is_active'     => 1,
                'size_required' => 0,
                'size'          => null,
            ]);
        }
    }
}
