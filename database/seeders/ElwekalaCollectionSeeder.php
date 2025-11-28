<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElwekalaCollection;
use Illuminate\Support\Facades\DB;

class ElwekalaCollectionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ElwekalaCollection::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        ElwekalaCollection::factory()->count(50)->create();
    }
}
