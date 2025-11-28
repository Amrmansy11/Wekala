<?php

namespace Database\Factories;

use App\Models\Feed;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::inRandomOrder()->first()?->id,
            'type' => $this->faker->randomElement(['story', 'feed']),
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (Feed $feed) {
            $feedImages = [
                public_path('feeds/feed1.jpg'),
                public_path('feeds/feed2.png'),
                public_path('feeds/feed3.jpg'),
                public_path('feeds/feed4.jpg'),
            ];

            $image = $this->faker->randomElement($feedImages);
            $feed->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection('feed_media');

            $products = Product::where('vendor_id', $feed->vendor_id)
                ->inRandomOrder()->take(5)->pluck('id');
            $feed->products()->attach($products);
        });
    }
}
