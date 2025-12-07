<?php

namespace App\Repositories\Vendor;

use App\Models\Product;
use App\Models\ProductMeasurement;
use App\Models\ProductSize;
use App\Models\ProductVariant;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    protected Model $model;
    protected SizeTemplateRepository $sizeTemplateRepository;

    public function __construct(Product $model, SizeTemplateRepository $sizeTemplateRepository)
    {
        $this->model = $model;
        $this->sizeTemplateRepository = $sizeTemplateRepository;
        parent::__construct($model);
    }

    /**
     * @param array $data
     * @return Product
     */
    public function store(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $publishedAt = $data['publish_type'] ? now() : $data['publish_date'];
            $templateId = $data['template_id'] ?? null;
            unset($data['publish_type'], $data['publish_date'], $data['template_id']);

            $product = new Product(array_merge($data, [
                'published_at' => $publishedAt,
            ]));
            $product->creatable()->associate(auth()->user());
            $product->save();

            if (!empty($data['tags']) && is_array($data['tags'])) {
                $product->tags()->attach($data['tags']);
            }

            if (!empty($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if ($image instanceof UploadedFile) {
                        $product->addMedia($image)->toMediaCollection('images');
                    }
                }
            }

            // Load category to check if it's clothing
            $product->load('category');

            if ($product->isClothing()) {
                $sizeIds = [];
                if (!empty($data['sizes']) && is_array($data['sizes'])) {
                    foreach ($data['sizes'] as $sizeData) {
                        $normalizedSize = strtoupper(trim($sizeData['size']));
                        $size = ProductSize::query()->firstOrCreate(
                            [
                                'size' => $normalizedSize,
                                'product_id' => $product->id,
                            ],
                            [
                                'pieces_per_bag' => $sizeData['pieces_per_bag'] ?? null,
                            ]
                        );
                        $sizeIds[] = $size->id;
                    }
                }
                if (!empty($data['colors']) && is_array($data['colors'])) {
                    foreach ($data['colors'] as $colorData) {
                        if($data['type'] == 'b2c'){
                            $colorData['quantity_b2c'] = $colorData['bags'];
                        }
                        /** @var ProductVariant $variant */
                        $variant = $product->variants()->create([
                            'color' => $colorData['color'],
                            'bags' => $colorData['bags'],
                            'total_pieces' => 0,
                            'quantity_b2c' => $colorData['quantity_b2c'] ?? 0,
                            'quantity_b2b' => $colorData['quantity_b2b'] ?? 0,
                        ]);

                        // Handle multiple images for the variant
                        if (isset($colorData['images']) && is_array($colorData['images'])) {
                            foreach ($colorData['images'] as $image) {
                                if ($image instanceof UploadedFile) {
                                    $variant->addMedia($image)->toMediaCollection('variant_images');
                                }
                            }
                        }


                        // Attach sizes with initial quantities
                        foreach ($sizeIds as $sizeId) {
                            $size = ProductSize::query()->find($sizeId);
                            $quantity = $colorData['bags'] * ($size->pieces_per_bag ?? 0);
                            $totalQuantity = $quantity * $variant->quantity_b2c;
                            $variant->sizes()->attach($sizeId, [
                                'quantity' => $quantity,
                                'total_quantity' => $totalQuantity,
                            ]);
                            $variant->total_pieces += $quantity;
                        }
                        // Calculate total_pieces_b2c and total_pieces_b2b
                        $variant->total_pieces_b2c = $variant->total_pieces * $variant->quantity_b2c;
                        $variant->total_pieces_b2b = $variant->total_pieces * $variant->quantity_b2b;
                        $variant->save();
                    }
                }

                // Handle template-based measurements
                $sizeTemplate = null;
                $templateData = [];

                // Get template if template_id is provided
                if (!empty($data['template_id'])) {
                    $sizeTemplate = $this->sizeTemplateRepository->find($data['template_id']);
                }

                // Use template_data if provided, otherwise use template
                if (!empty($data['template_data'])) {
                    $templateData = $data['template_data'];
                } elseif ($sizeTemplate) {
                    $templateData = [
                        'chest' => $sizeTemplate->chest,
                        'chest_pattern' => $sizeTemplate->chest_pattern,
                        'product_length' => $sizeTemplate->product_length,
                        'length_pattern' => $sizeTemplate->length_pattern,
                        'weight_from' => $sizeTemplate->weight_from,
                        'weight_from_pattern' => $sizeTemplate->weight_from_pattern,
                        'weight_to' => $sizeTemplate->weight_to,
                        'weight_to_pattern' => $sizeTemplate->weight_to_pattern,
                    ];
                }

                // Calculate measurements from template
                if (!empty($templateData) && !empty($sizeIds)) {
                    // Get size names from product sizes
                    $sizeNames = array_map(function ($sizeId) {
                        $size = ProductSize::query()->find($sizeId);
                        return strtoupper(trim($size->size));
                    }, $sizeIds);

                    // If we have a sizeTemplate, use it for calculating patterns
                    if ($sizeTemplate) {
                        $sizePatterns = $this->sizeTemplateRepository->patterns($sizeNames, $sizeTemplate);
                    } else {
                        // Otherwise, calculate directly from template_data
                        $sizePatterns = $this->calculateMeasurementsFromData($sizeNames, $templateData);
                    }

                    // Save calculated measurements
                    foreach ($sizePatterns as $pattern) {
                        ProductMeasurement::query()->firstOrCreate(
                            [
                                'size' => $pattern['size'],
                                'product_id' => $product->id,
                            ],
                            [
                                'chest' => $pattern['chest'],
                                'length' => $pattern['length'],
                                'weight_range' => $pattern['weight_from'] . ' - ' . $pattern['weight_to'],
                                'waist' => null,
                                'bundles' => null,
                            ]
                        );
                    }
                }
            } else {
                $product->stock_b2b = $data['stock_b2b'] ?? 0;
                $product->stock_b2c = $data['stock_b2c'] ?? 0;
                $product->stock = $product->stock_b2b + $product->stock_b2c;
            }
            $product->save();
            return $product->load('category', 'brand', 'tags', 'sizes', 'variants');
        });
    }

    public function show($id, $typeGuard = 'vendor-api', $type = null): ?Product
    {
        $product = $this->model->query()
            ->withExists(['wishlists as is_fav' => function ($q) use ($typeGuard) {
                $q->where('userable_id', auth($typeGuard)->id());
                // ->where('userable_type', get_class(auth($typeGuard)->user()));
            }])
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            })
            ->find($id);
        if (!$product) {
            return null;
        }
        return $product->load('category', 'brand', 'tags', 'sizes', 'variants','reviews');
    }

    /**
     * @param array $data
     * @param int|string $modelId
     * @return Product
     */
    public function update(array $data, int|string $modelId): Product
    {
        return DB::transaction(function () use ($data, $modelId) {
            $product = $this->model->query()->findOrFail($modelId);

            $publishedAt = $data['publish_type'] ? now() : $data['publish_date'];
            unset($data['publish_type'], $data['publish_date']);

            $product->update(array_merge($data, [
                'published_at' => $publishedAt,
            ]));

            // Update tags
            if (array_key_exists('tags', $data)) {
                $product->tags()->sync($data['tags'] ?? []);
            }

            // Handle images
            if (!empty($data['images']) && is_array($data['images'])) {
                // Clear existing images and add new ones
                $product->clearMediaCollection('images');
                foreach ($data['images'] as $image) {
                    if ($image instanceof UploadedFile) {
                        $product->addMedia($image)->toMediaCollection('images');
                    }
                }
            }

            if ($product->isClothing()) {
                // Update sizes
                if (!empty($data['sizes']) && is_array($data['sizes'])) {
                    // Remove existing sizes
                    $product->sizes()->delete();

                    $sizeIds = [];
                    foreach ($data['sizes'] as $sizeData) {
                        $normalizedSize = strtoupper(trim($sizeData['size']));
                        /** @var ProductSize $size */
                        $size = ProductSize::query()->create([
                            'size' => $normalizedSize,
                            'product_id' => $product->id,
                            'pieces_per_bag' => $sizeData['pieces_per_bag'] ?? null,
                        ]);
                        $sizeIds[] = $size->id;
                    }
                }

                // Update colors/variants
                if (!empty($data['colors']) && is_array($data['colors'])) {
                    // Remove existing variants
                    $product->variants()->delete();

                    foreach ($data['colors'] as $colorData) {
                        if($data['type'] == 'b2c'){
                            $colorData['quantity_b2c'] = $colorData['bags'];
                        }
                        /** @var ProductVariant $variant */
                        $variant = $product->variants()->create([
                            'color' => $colorData['color'],
                            'bags' => $colorData['bags'],
                            'total_pieces' => 0,
                            'quantity_b2c' => $colorData['quantity_b2c'] ?? 0,
                            'quantity_b2b' => $colorData['quantity_b2b'] ?? 0,
                        ]);

                        // Handle multiple images for the variant
                        if (isset($colorData['images']) && is_array($colorData['images'])) {
                            foreach ($colorData['images'] as $image) {
                                if ($image instanceof UploadedFile) {
                                    $variant->addMedia($image)->toMediaCollection('variant_images');
                                }
                            }
                        } elseif (isset($colorData['image']) && $colorData['image'] instanceof UploadedFile) {
                            $variant->addMedia($colorData['image'])->toMediaCollection('variant_images');
                        }

                        // Attach sizes with initial quantities
                        foreach ($sizeIds as $sizeId) {
                            $size = ProductSize::query()->find($sizeId);
                            $quantity = $colorData['bags'] * ($size->pieces_per_bag ?? 0);
                            $totalQuantity = $quantity * $variant->quantity_b2c;
                            $variant->sizes()->attach($sizeId, [
                                'quantity' => $quantity,
                                'total_quantity' => $totalQuantity,
                            ]);
                            $variant->total_pieces += $quantity;
                        }
                        // Calculate total_pieces_b2c and total_pieces_b2b
                        $variant->total_pieces_b2c = $variant->total_pieces * $variant->bags;
                        $variant->total_pieces_b2b = $variant->total_pieces * $variant->bags;
                        $variant->save();
                    }
                }

                // Update measurements
                if (!empty($data['measurements']) && is_array($data['measurements'])) {
                    // Remove existing measurements
                    $product->productMeasurement()->delete();

                    foreach ($data['measurements'] as $measurementData) {
                        $normalizedSize = strtoupper(trim($measurementData['size']));
                        ProductMeasurement::query()->create([
                            'size' => $normalizedSize,
                            'product_id' => $product->id,
                            'waist' => $measurementData['waist'] ?? null,
                            'length' => $measurementData['length'] ?? null,
                            'chest' => $measurementData['chest'] ?? null,
                            'weight_range' => $measurementData['weight_range'] ?? null,
                            'bundles' => $measurementData['bundles'] ?? null,
                        ]);
                    }
                }
            } else {
                $product->stock_b2b = $data['stock_b2b'] ?? 0;
                $product->stock_b2c = $data['stock_b2c'] ?? 0;
                $product->stock = $product->stock_b2b + $product->stock_b2c;
            }

            $product->save();
            return $product->load('category', 'brand', 'tags', 'sizes', 'variants');
        });
    }


    /**
     * Calculate measurements from template data without a size template
     */
    private function calculateMeasurementsFromData(array $sizeNames, array $templateData): array
    {
        $patterns = [];

        foreach ($sizeNames as $index => $sizeName) {
            // Calculate measurements based on size position and patterns
            $chest = isset($templateData['chest']) ? $templateData['chest'] + ($templateData['chest_pattern'] ?? 0) * ($index + 1) : null;
            $length = isset($templateData['product_length']) ? $templateData['product_length'] + ($templateData['length_pattern'] ?? 0) * ($index + 1) : null;
            $weightFrom = isset($templateData['weight_from']) ? $templateData['weight_from'] + ($templateData['weight_from_pattern'] ?? 0) * ($index + 1) : null;
            $weightTo = isset($templateData['weight_to']) ? $templateData['weight_to'] + ($templateData['weight_to_pattern'] ?? 0) * ($index + 1) : null;

            $patterns[] = [
                'size' => $sizeName,
                'chest' => $chest,
                'length' => $length,
                'weight_from' => $weightFrom,
                'weight_to' => $weightTo,
            ];
        }

        return $patterns;
    }
}
