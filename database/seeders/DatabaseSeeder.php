<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
