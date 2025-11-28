<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Size;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Size::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        $categories = Category::query()->whereNull('parent_id')->get();

        foreach ($sizes as $index => $size) {
            Size::query()->create([
                'name' => $size,
                'category_id' => $categories->random()->id,
                'is_active' => true,
                'order' => $index + 1,
            ]);
        }
    }
}
