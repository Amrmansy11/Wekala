<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Color;
use Illuminate\Support\Facades\DB;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Color::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $colors = [
            ['en' => 'Red', 'ar' => 'أحمر', 'hex' => '#FF0000'],
            ['en' => 'Green', 'ar' => 'أخضر', 'hex' => '#00FF00'],
            ['en' => 'Blue', 'ar' => 'أزرق', 'hex' => '#0000FF'],
            ['en' => 'Black', 'ar' => 'أسود', 'hex' => '#000000'],
            ['en' => 'White', 'ar' => 'أبيض', 'hex' => '#FFFFFF'],
            ['en' => 'Yellow', 'ar' => 'أصفر', 'hex' => '#FFFF00'],
            ['en' => 'Purple', 'ar' => 'أرجواني', 'hex' => '#800080'],
            ['en' => 'Orange', 'ar' => 'برتقالي', 'hex' => '#FFA500'],
            ['en' => 'Pink', 'ar' => 'وردي', 'hex' => '#FFC0CB'],
            ['en' => 'Brown', 'ar' => 'بني', 'hex' => '#8B4513'],
            ['en' => 'Grey', 'ar' => 'رمادي', 'hex' => '#808080'],
            ['en' => 'Cyan', 'ar' => 'سماوي', 'hex' => '#00FFFF'],
            ['en' => 'Magenta', 'ar' => 'أرجواني فاتح', 'hex' => '#FF00FF'],
            ['en' => 'Maroon', 'ar' => 'خمري', 'hex' => '#800000'],
            ['en' => 'Olive', 'ar' => 'زيتوني', 'hex' => '#808000'],
            ['en' => 'Navy', 'ar' => 'كحلي', 'hex' => '#000080'],
            ['en' => 'Teal', 'ar' => 'فيروزي', 'hex' => '#008080'],
            ['en' => 'Lavender', 'ar' => 'لافندر', 'hex' => '#E6E6FA'],
            ['en' => 'Beige', 'ar' => 'بيج', 'hex' => '#F5F5DC'],
            ['en' => 'Turquoise', 'ar' => 'تركواز', 'hex' => '#40E0D0'],
            ['ar' => 'فضي', 'en' => 'Silver', 'hex' => '#C0C0C0'],
        ];
        foreach ($colors as $color) {
            Color::create([
                'name' => ['en' => $color['en'], 'ar' => $color['ar']],
                'hex_code' => $color['hex'],
                'color' => strtolower($color['en']),
                'is_active' => true,
            ]);
        }
    }
}
