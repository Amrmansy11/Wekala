<?php

namespace Database\Factories;

use App\Models\Gift;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class GiftFactory extends Factory
{
    protected $model = Gift::class;

    public function definition(): array
    {
        $vendorId = 92; // Vendor::inRandomOrder()->value('id') ?? Vendor::factory()->create()->id;

        $source = Product::where('vendor_id', $vendorId)
            ->inRandomOrder()
            ->B2BB2C()
            ->first();

        $gift = Product::where('vendor_id', $vendorId)
            ->where('id', '!=', optional($source)->id)
            ->inRandomOrder()
            ->B2BB2C()
            ->first();

        if (!$source) {
            $source = Product::factory()->create(['vendor_id' => $vendorId]);
        }

        if (!$gift) {
            $gift = Product::factory()->create(['vendor_id' => $vendorId]);
        }

        return [
            'vendor_id'         => $vendorId,
            'source_product_id' => $source->id,
            'gift_product_id'   => $gift->id,

            'starts_at' => now()->subDays(rand(0, 5)),
            'ends_at'   => now()->addDays(rand(1, 10)),

            'is_active' => true,

            'archived_at' => null,
        ];
    }


    public function archived()
    {
        return $this->state(fn() => [
            'archived_at' => now(),
        ]);
    }


    public function inactive()
    {
        return $this->state(fn() => [
            'is_active' => false,
        ]);
    }
}
