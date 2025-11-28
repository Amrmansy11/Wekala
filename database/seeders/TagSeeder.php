<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Tag::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $tags = [
            ['en' => 'Casual',   'ar' => 'كاجوال'],
            ['en' => 'Formal',   'ar' => 'رسمي'],
            ['en' => 'Summer',   'ar' => 'صيفي'],
            ['en' => 'Winter',   'ar' => 'شتوي'],
            ['en' => 'Sport',    'ar' => 'رياضي'],
            ['en' => 'Workwear', 'ar' => 'ملابس عمل'],
            ['en' => 'Party',    'ar' => 'حفلات'],
            ['en' => 'Trendy',   'ar' => 'عصري'],
            ['en' => 'Classic',  'ar' => 'كلاسيكي'],
            ['en' => 'Basic',    'ar' => 'أساسي'],
        ];
        $categories = Category::query()->whereNull('parent_id')->get();

        foreach ($categories as $category) {
            foreach ($tags as $tag) {
                Tag::query()->create([
                    'name'        => $tag,
                    'is_active'   => true,
                    'category_id' => $category->id,
                ]);
            }
        }
    }
}
