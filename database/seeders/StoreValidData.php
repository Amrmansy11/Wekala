<?php

namespace Database\Seeders;

use App\Models\Feed;
use App\Models\Offer;
use App\Models\Point;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Voucher;
use App\Models\Discount;
use App\Models\VendorUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreValidData extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        VendorUser::query()->truncate();
        Vendor::query()->truncate();
        Product::query()->truncate();
        Voucher::query()->truncate();
        Offer::query()->truncate();
        Feed::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $vendorUser = VendorUser::query()->create([
            'name' => 'H&M',
            'email' => 'h&m@gmail.com',
            'phone' => '01100223344',
            'password' => bcrypt('password'),
            'main_account' => 1,
            'is_active' => 1,
        ]);
        $vendorUser->assignRole('Super Admin');

        $vendor = $vendorUser->vendor()->create([
            'store_type' => 'seller',
            'store_name' => 'H&M',
            'phone' => '01100223355',
            'category_id' => 32,
            'state_id' => 1,
            'city_id' => 1,
            'address' => [
                'en' => 'Cairo, Egypt',
                'ar' => 'القاهرة، مصر',
            ],
            'description' => [
                'en' => 'This store sells clothes and accessories.',
                'ar' => 'هذا المتجر يبيع الملابس والإكسسوارات.',
            ],
            'status' => 'pending',
        ]);
        $vendorUser->update(['vendor_id' => $vendor->id]);

        $vendorFiles = [
            'vendor_logo' => 'brands/Womens/HM.png',
            'national_id_file' => 'brands/Womens/HM.png',
            'vendor_tax_card' => 'brands/Womens/HM.png',
        ];
        foreach ($vendorFiles as $collection => $filePath) {
            $vendor->addMedia(public_path($filePath))
                ->usingName($vendorUser->name)
                ->preservingOriginal()
                ->toMediaCollection($collection);
        }

        Product::factory()->count(10)->create([
            'vendor_id' => $vendor->id,
            'category_id' => 32,
        ]);

        Voucher::factory()->count(10)->create([
            'creatable_type' => Vendor::class,
            'creatable_id' => $vendor->id,
        ]);
        Discount::factory()->count(10)->withProducts(4)->create([
            'vendor_id' => $vendor->id,
        ]);
        Offer::factory()->count(10)->create([
            'creatable_type' => Vendor::class,
            'creatable_id' => $vendor->id,
        ]);

        Point::factory()->count(10)->withProducts(4)->create([
            'vendor_id' => $vendor->id,
        ]);

        Feed::factory()->count(10)->create([
            'vendor_id' => $vendor->id,
        ]);
    }
}
