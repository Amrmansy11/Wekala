<?php

namespace Database\Seeders;

use Database\Seeders\TagSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\SizeSeeder;
use Database\Seeders\AdminSeeder;
use Database\Seeders\ColorSeeder;
use Database\Seeders\StateSeeder;
use Database\Seeders\SliderSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MaterialSeeder;
use Database\Seeders\PackingUnitSeeder;
use Database\Seeders\ElwekalaCollectionSeeder;
use Database\Seeders\AdminRoleAndPermissionSeeder;
use Database\Seeders\VendorsRoleAndPermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            CategorySeeder::class,
            ColorSeeder::class,
            MaterialSeeder::class,
            SizeSeeder::class,
            TagSeeder::class,
            AdminRoleAndPermissionSeeder::class,
            VendorsRoleAndPermissionSeeder::class,
            AdminSeeder::class,
            StateSeeder::class,
            // ElwekalaCollectionSeeder::class,
            SliderSeeder::class,
            PackingUnitSeeder::class,
        ]);
    }
}
