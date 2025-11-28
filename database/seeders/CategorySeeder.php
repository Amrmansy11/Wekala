<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::query()->truncate();
        Brand::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->call([
            SportsOutdoorsCategorySeeder::class,
            WomansCategorySeeder::class,
            HomeGardenCategorySeeder::class,
            HealthBeautyCategorySeeder::class,
            MenCategorySeeder::class
        ]);
    }
}
