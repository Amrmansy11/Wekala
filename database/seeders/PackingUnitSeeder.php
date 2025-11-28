<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackingUnit;

class PackingUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'name' => ['en' => 'Box', 'ar' => 'صندوق'],
                'is_active' => true,
                'category_id' => 1,
            ],
            [
                'name' => ['en' => 'Bag', 'ar' => 'كيس'],
                'is_active' => true,
                'category_id' => 1,
            ],
            [
                'name' => ['en' => 'Carton', 'ar' => 'كرتونة'],
                'is_active' => true,
                'category_id' => 2,
            ],
            [
                'name' => ['en' => 'Bottle', 'ar' => 'زجاجة'],
                'is_active' => false,
                'category_id' => 2,
            ],
        ];

        foreach ($units as $unit) {
            PackingUnit::create($unit);
        }
    }
}
