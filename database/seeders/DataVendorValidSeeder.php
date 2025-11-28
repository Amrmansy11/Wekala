<?php

namespace Database\Seeders;

use App\Models\Feed;
use App\Models\Gift;
use App\Models\Offer;
use App\Models\Point;
use App\Models\Voucher;
use App\Models\Discount;
use Illuminate\Database\Seeder;

class DataVendorValidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gift::factory()->create();
        // Voucher::factory(10)->create();
        // Discount::factory(10)->withProducts(4)->create();
        Point::factory(10)->withProducts(1)->create();
        // Offer::factory(10)->quantity()->create();
        // Offer::factory(10)->purchase()->create();
        // Offer::factory(10)->custom()->create();
        // Feed::factory(10)->create();
    }
}
