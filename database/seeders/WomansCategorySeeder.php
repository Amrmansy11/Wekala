<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;

class WomansCategorySeeder extends Seeder
{
    public function run(): void
    {
        $womens = Category::query()->create([
            'name'          => ['en' => 'Women\'s Clothing', 'ar' => 'ملابس نسائية'],
            'parent_id'     => null,
            'is_active'     => 1,
            'size_required' => 1,
            'size'          => null,
        ]);

        $brandsWomanMainCategory = [

            ['en' => 'Anthropologie', 'ar' => 'أنتروبولوجي', 'logo' => 'brands/Womens/Anthropologie.png'],
            ['en' => 'Reformation', 'ar' => 'ريفورميشن', 'logo' => 'brands/Womens/Reformation.png'],
            ['en' => 'Sézane', 'ar' => 'سيزان', 'logo' => 'brands/Womens/Sézane.jpg'],
            ['en' => 'Mac Duggal', 'ar' => 'ماك دوجال', 'logo' => 'brands/Womens/MacDuggal.png'],
            ['en' => 'Jovani', 'ar' => 'جوفاني', 'logo' => 'brands/Womens/Jovani.png'],
            ['en' => 'La Femme', 'ar' => 'لا فام', 'logo' => 'brands/Womens/LaFemme.png'],
            ['en' => 'BHLDN', 'ar' => 'بيه إل دي إن', 'logo' => 'brands/Womens/BHLDN.png'],
            ['en' => 'Theory', 'ar' => 'ثيوري', 'logo' => 'brands/Womens/Theory.jpg'],
            ['en' => 'Eliza J', 'ar' => 'إليزا ج', 'logo' => 'brands/Womens/ElizaJ.png'],
            ['en' => 'Akris', 'ar' => 'أكريس', 'logo' => 'brands/Womens/Akris.jpg'],
            ['en' => 'Vince Camuto', 'ar' => 'فينس كاموتو', 'logo' => 'brands/Womens/VinceCamuto.png'],
            ['en' => 'Madewell', 'ar' => 'ماديويل', 'logo' => 'brands/Womens/Madewell.png'],
            ['en' => 'Aritzia', 'ar' => 'أريتزيا', 'logo' => 'brands/Womens/Aritzia.png'],
            ['en' => 'H&M', 'ar' => 'إتش آند إم', 'logo' => 'brands/Womens/HM.png'],
            ['en' => 'Zara', 'ar' => 'زارا أبركرومبي', 'logo' => 'brands/Womens/zara.png'],
            ['en' => 'SKIMS', 'ar' => 'سكيمز', 'logo' => 'brands/Womens/SKIMS.png'],
            ['en' => 'COS', 'ar' => 'كوس', 'logo' => 'brands/Womens/COS.jpg'],
            ['en' => 'Everlane', 'ar' => 'إفرلين', 'logo' => 'brands/Womens/Everlane.png'],
            ['en' => 'Uniqlo', 'ar' => 'يونيكلو', 'logo' => 'brands/Womens/Uniqlo.png'],
            ['en' => 'Good American', 'ar' => 'جود أمريكان', 'logo' => 'brands/Womens/GoodAmerican.png'],
            ['en' => 'lululemon', 'ar' => 'لولو ليمن', 'logo' => 'brands/Womens/lululemon.png'],

        ];
        foreach ($brandsWomanMainCategory as $brand) {
            $brandmodel = Brand::query()->create([
                'name'          => $brand,
                'category_id'   => $womens->id,
                'is_active'     => 1,
                'creatable_type' => 'App\Models\Admin',
                'creatable_id'   => 1
            ]);
            $brandmodel->clearMediaCollection('brand_logo');
            $brandmodel->addMedia(public_path($brand['logo']))
                ->usingName($brandmodel->getTranslation('name', 'en'))
                ->preservingOriginal()
                ->toMediaCollection('brand_logo');
        }
        $dresses = Category::query()->create([
            'name'          => ['en' => 'Dresses', 'ar' => 'فساتين'],
            'parent_id'     => $womens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $dressesSub = [
            ['en' => 'Sundresses', 'ar' => 'فساتين صيفية', 'size' => 'tshirt', 'image' => 'category/Womens/Sundresses.jpg'],
            ['en' => 'Shirt Dresses', 'ar' => 'فساتين قميص', 'size' => 'tshirt', 'image' => 'category/Womens/ShirtDresses.jpg'],
            ['en' => 'T-shirt Dresses', 'ar' => 'فساتين تيشيرت', 'size' => 'tshirt', 'image' => 'category/Womens/ShirtDresses.jpg'],
            ['en' => 'Cocktail', 'ar' => 'سهرة قصيرة', 'size' => 'tshirt', 'image' => 'category/Womens/Cocktail.jpg'],
            ['en' => 'Evening', 'ar' => 'سهرة مسائية', 'size' => 'tshirt', 'image' => 'category/Womens/Cocktail.jpg'],
            ['en' => 'Prom', 'ar' => 'حفلات التخرج', 'size' => 'tshirt', 'image' => 'category/Womens/Cocktail.jpg'],
            ['en' => 'Sheath', 'ar' => 'شياث', 'size' => 'tshirt', 'image' => 'category/Womens/Aline.jpg'],
            ['en' => 'A-line', 'ar' => 'قَصّة A', 'size' => 'tshirt', 'image' => 'category/Womens/Aline.jpg'],
        ];

        foreach ($dressesSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $dresses->id,
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
        $tops = Category::query()->create([
            'name'          => ['en' => 'Tops', 'ar' => 'قمم'],
            'parent_id'     => $womens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $topsSub = [
            ['en' => 'Button-down', 'ar' => 'قميص بأزرار', 'size' => 'tshirt', 'image' => 'category/Womens/Buttondown.jpg'],
            ['en' => 'Sleeveless', 'ar' => 'بلا أكمام', 'size' => 'tshirt', 'image' => 'category/Womens/Sleeveless.jpg'],
            ['en' => 'Peasant', 'ar' => 'بلوزة فلاحية', 'size' => 'tshirt', 'image' => 'category/Womens/Peasant.jpg'],
            ['en' => 'Graphic Tees', 'ar' => 'تيشيرتات جرافيك', 'size' => 'tshirt', 'image' => 'category/Womens/GraphicTees.jpg'],
            ['en' => 'V-neck', 'ar' => 'ياقة V', 'size' => 'tshirt', 'image' => 'category/Womens/GraphicTees.jpg'],
            ['en' => 'Camisoles', 'ar' => 'قمصان داخلية', 'size' => 'tshirt', 'image' => 'category/Womens/Sleeveless.jpg'],
            ['en' => 'Pullover', 'ar' => 'كنزات صوفية', 'size' => 'tshirt', 'image' => 'category/Womens/GraphicTees.jpg'],
            ['en' => 'Turtleneck', 'ar' => 'ياقة عالية', 'size' => 'tshirt', 'image' => 'category/Womens/Buttondown.jpg'],
            ['en' => 'Knit', 'ar' => 'محبوك', 'size' => 'tshirt', 'image' => 'category/Womens/GraphicTees.jpg'],
        ];

        foreach ($topsSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $tops->id,
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

        $bottoms = Category::query()->create([
            'name'          => ['en' => 'Bottoms', 'ar' => 'قطع سفلية'],
            'parent_id'     => $womens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $bottomsSub = [
            ['en' => 'Skinny', 'ar' => 'بنطال ضيق', 'size' => 'pants'],
            ['en' => 'Straight Leg', 'ar' => 'رجل مستقيمة', 'size' => 'pants'],
            ['en' => 'Bootcut', 'ar' => 'بوت كات', 'size' => 'pants'],
            ['en' => 'Trousers', 'ar' => 'سراويل', 'size' => 'pants'],
            ['en' => 'Wide-leg', 'ar' => 'أرجل واسعة', 'size' => 'pants'],
            ['en' => 'Joggers', 'ar' => 'بنطال رياضي', 'size' => 'pants'],
            ['en' => 'Mini', 'ar' => 'قصيرة', 'size' => 'pants'],
            ['en' => 'Midi', 'ar' => 'متوسطة', 'size' => 'pants'],
            ['en' => 'Maxi', 'ar' => 'طويلة', 'size' => 'pants'],
            ['en' => 'Denim', 'ar' => 'دينم', 'size' => 'pants'],
            ['en' => 'Bermuda', 'ar' => 'برمودا', 'size' => 'pants'],
            ['en' => 'Athletic', 'ar' => 'رياضية', 'size' => 'pants'],
        ];

        foreach ($bottomsSub as $item) {
            Category::query()->create([
                'name'          => $item,
                'parent_id'     => $bottoms->id,
                'is_active'     => 1,
                'size_required' => 1,
                'size'          => $item['size'],
            ]);
        }

        $outerwear = Category::query()->create([
            'name'          => ['en' => 'Outerwear', 'ar' => 'ملابس خارجية'],
            'parent_id'     => $womens->id,
            'is_active'     => 1,
            'size_required' => 0,
            'size'          => null,
        ]);

        $outerwearSub = [
            ['en' => 'Denim', 'ar' => 'دينم', 'size' => 'tshirt', 'image' => 'category/Womens/Denim.jpg'],
            ['en' => 'Leather', 'ar' => 'جلد', 'size' => 'tshirt', 'image' => 'category/Womens/Leather.jpg'],
            ['en' => 'Bomber', 'ar' => 'بومبر', 'size' => 'tshirt', 'image' => 'category/Womens/Bomber.jpg'],
            ['en' => 'Trench', 'ar' => 'ترنش', 'size' => 'tshirt', 'image' => 'category/Womens/Trench.jpg'],
            ['en' => 'Pea Coat', 'ar' => 'بي كوت', 'size' => 'tshirt', 'image' => 'category/Womens/PeaCoat.jpg'],
            ['en' => 'Puffer', 'ar' => 'منفوخ', 'size' => 'tshirt', 'image' => 'category/Womens/Puffer.jpg'],
            ['en' => 'Blazers', 'ar' => 'بليزر', 'size' => 'tshirt', 'image' => 'category/Womens/Blazers.jpg'],
        ];

        foreach ($outerwearSub as $item) {
            $category = Category::query()->create([
                'name'          => $item,
                'parent_id'     => $outerwear->id,
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
