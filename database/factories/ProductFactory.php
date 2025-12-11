<?php

namespace Database\Factories;

use App\Models\Tag;
use App\Models\Brand;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Category;
use App\Models\VendorUser;
use App\Models\ProductSize;
use App\Enums\ProductStatus;
use Illuminate\Support\Carbon;
use App\Models\ProductMeasurement;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $category = Category::query()->whereNull('parent_id')->inRandomOrder()->first();

        return [
            'name' => $this->faker->word() . ' T-Shirt',
            'description' => $this->faker->sentence(),
            'barcode' => uniqid(),
            'wholesale_price' => $this->faker->numberBetween(50, 200),
            'consumer_price' => $this->faker->numberBetween(150, 300),
            'category_id' => 32,
            'sub_category_id' => 33,
            'sub_sub_category_id' => 34,
            'brand_id' => fn($attrs) => Brand::query()->whereNull('vendor_id')->where('category_id', $attrs['category_id'])->inRandomOrder()->first()->id,
            'material_id' => 1,
            'stock' => $this->faker->numberBetween(100, 1000),
            'stock_b2b' => $this->faker->numberBetween(50, 500),
            'stock_b2c' => $this->faker->numberBetween(50, 500),
            'min_color' => $this->faker->numberBetween(1, 10),
            'published_at' => Carbon::now(),
            'status' => $this->faker->randomElement(ProductStatus::toArray()),
            'elwekala_policy' => true,
            'vendor_id' => fn() => $this->randomVendorId(),
            'creatable_type' => VendorUser::class,
            // 'creatable_id' => fn($attrs) => VendorUser::query()->where('vendor_id', $attrs['vendor_id'])->inRandomOrder()->first()->id,
            'creatable_id' => 149,
            'type' => $this->faker->randomElement(['b2c', 'b2b_b2c']),
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Product $product) {

            // Tags
            $tags = Tag::query()
                ->where('category_id', $product->category_id)
                ->inRandomOrder()
                ->take(4)
                ->pluck('id')
                ->toArray();
            $product->tags()->attach($tags);

            // Sizes
            $sizeNames = ['XS', 'S', 'M', 'L', 'XL'];
            $sizeIds = [];
            foreach ($sizeNames as $sizeName) {
                $size = ProductSize::query()->firstOrCreate(
                    ['size' => $sizeName, 'product_id' => $product->id],
                    ['pieces_per_bag' => rand(1, 10)]
                );
                $sizeIds[] = $size->id;
            }

            // Variants
            $colors = ['Red', 'Blue', 'Green', 'Black', 'White'];
            foreach ($colors as $color) {
                $variant = $product->variants()->create([
                    'color' => $color,
                    'bags' => rand(1, 5),
                    'total_pieces' => 0,
                ]);

                // Attach sizes with quantity
                foreach ($sizeIds as $sizeId) {
                    $size = ProductSize::query()->find($sizeId);
                    $quantity = $variant->bags * ($size->pieces_per_bag ?? 1);
                    $variant->sizes()->attach($sizeId, ['quantity' => $quantity]);
                    $variant->total_pieces += $quantity;
                }
                $variant->save();
            }

            // Measurements for clothing
            if ($product->isClothing()) {
                foreach ($sizeNames as $sizeName) {
                    ProductMeasurement::query()->firstOrCreate(
                        ['size' => $sizeName, 'product_id' => $product->id],
                        [
                            'waist' => rand(24, 40),
                            'length' => rand(20, 40),
                            'chest' => rand(30, 50),
                            'weight_range' => '50-70kg',
                        ]
                    );
                }
            }
        });
    }

    private function randomVendorId(): ?int
    {
        $vendor = Vendor::query()->inRandomOrder()->first();
        return $vendor ? $vendor->id : null;
    }
}
