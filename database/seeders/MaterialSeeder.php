<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Material;
use Illuminate\Support\Facades\DB;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Material::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $materials = [
            ['en' => 'Cotton',    'ar' => 'قطن'],
            ['en' => 'Linen',     'ar' => 'كتان'],
            ['en' => 'Silk',      'ar' => 'حرير'],
            ['en' => 'Wool',      'ar' => 'صوف'],
            ['en' => 'Polyester', 'ar' => 'بوليستر'],
            ['en' => 'Denim',     'ar' => 'دنيم'],
            ['en' => 'Leather',   'ar' => 'جلد'],
            ['en' => 'Velvet',    'ar' => 'مخمل'],
            ['en' => 'Chiffon',   'ar' => 'شيفون'],
            ['en' => 'Satin',     'ar' => 'ساتان'],
            ['en' => 'Lycra',     'ar' => 'ليكرا'],
            ['en' => 'Nylon',     'ar' => 'نايلون'],
            ['en' => 'Cashmere',  'ar' => 'كشمير'],
            ['en' => 'Flannel',   'ar' => 'قماش فلانيل'],
            ['en' => 'Fleece',    'ar' => 'صوف صناعي'],
            ['en' => 'Suede',     'ar' => 'شامواه'],
            ['en' => 'Corduroy',  'ar' => 'قماش كوردروي'],
            ['en' => 'Jersey',    'ar' => 'قماش جيرسي'],
            ['en' => 'Tweed',     'ar' => 'تويد'],
            ['en' => 'Brocade',   'ar' => 'بروكاد'],
        ];
        foreach ($materials as $material) {
            Material::query()->create([
                'name'      => ['en' => $material['en'], 'ar' => $material['ar']],
                'is_active' => true
            ]);
        }
    }
}
