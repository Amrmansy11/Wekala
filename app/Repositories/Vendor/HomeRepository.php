<?php

namespace App\Repositories\Vendor;

use App\Models\Brand;
use Illuminate\Support\Collection;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;

class HomeRepository extends BaseRepository
{
    public function __construct() {}


    /**
     * @param int $limit
     * @return Collection
     */
    public function getFlashSales(int $limit = 6): Collection
    {
        return collect([
            ['id' => 1, 'title' => 'Flash Sale 1', 'image' => 'image1.jpg'],
            ['id' => 2, 'title' => 'Flash Sale 2', 'image' => 'image2.jpg'],
            ['id' => 3, 'title' => 'Flash Sale 3', 'image' => 'image3.jpg'],
        ]);
    }

    /**
     * @param int $limit
     * @return Collection
     */
    public function getCollections($limit = 4): Collection
    {
        return collect([
            ['id' => 1, 'title' => 'Collection 1', 'image' => 'collection1.jpg'],
            ['id' => 2, 'title' => 'Collection 2', 'image' => 'collection2.jpg'],
            ['id' => 3, 'title' => 'Collection 3', 'image' => 'collection3.jpg'],
            ['id' => 4, 'title' => 'Collection 4', 'image' => 'collection4.jpg'],
        ]);
    }

    /**
     * @param int $limit
     * @return Collection
     */
    public function getTopBrands($limit = 6): Collection
    {
        return Brand::where('is_active', true)
            ->take($limit)
            ->get();
    }

    /**
     * @param int $limit
     * @return Collection
     */
    public function getJustForYou($limit = 6): Collection
    {
        return collect([
            ['id' => 1, 'title' => 'Product 1', 'image' => 'product1.jpg', 'price' => 100, 'stock' => '100', 'color' => ['red', 'black']],
            ['id' => 2, 'title' => 'Product 2', 'image' => 'product2.jpg', 'price' => 200, 'stock' => '200', 'color' => ['blue', 'white']],
            ['id' => 3, 'title' => 'Product 3', 'image' => 'product3.jpg', 'price' => 300, 'stock' => '300', 'color' => ['green', 'red']],
            ['id' => 4, 'title' => 'Product 4', 'image' => 'product4.jpg', 'price' => 400, 'stock' => '250', 'color' => ['yellow', 'blue']],
            ['id' => 5, 'title' => 'Product 5', 'image' => 'product5.jpg', 'price' => 500, 'stock' => '50', 'color' => ['purple', 'black']],
            ['id' => 6, 'title' => 'Product 6', 'image' => 'product6.jpg', 'price' => 600, 'stock' => '400', 'color' => ['orange', 'yellow']],

        ]);
    }
}
