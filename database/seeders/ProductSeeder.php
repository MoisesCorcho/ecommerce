<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategoryImageFile('15.jpeg');

        $category = Category::query()->updateOrCreate(
            ['slug' => 'honeycomb'],
            [
                'name' => 'Honeycomb',
                'image_path' => 'categories/15.jpeg',
                'sort_order' => 0,
            ],
        );

        foreach ($this->products() as $data) {
            $product = Product::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'material' => $data['material'],
                    'dimensions' => null,
                    'is_preorder' => false,
                    'is_active' => true,
                ],
            );

            foreach ($data['colors'] as $color) {
                $sku = count($data['colors']) > 1
                    ? $data['sku'].'-'.Str::of($color)->slug('-')->upper()
                    : $data['sku'];

                $variant = $product->variants()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'color' => $color,
                        // Same physical bag across colors; carries the value formerly
                        // duplicated in products.dimensions until that column is dropped.
                        'size' => $data['dimensions'],
                        'stock' => $data['stock'],
                        'is_active' => true,
                    ],
                );

                $variant->prices()->updateOrCreate(
                    ['currency' => CurrencyEnum::Cop],
                    [
                        'price' => $data['price'],
                        'compare_at_price' => null,
                    ],
                );

                $eurPrice = (int) round(($data['price'] / 4000) * 100);
                $variant->prices()->updateOrCreate(
                    ['currency' => CurrencyEnum::Eur],
                    [
                        'price' => $eurPrice,
                        'compare_at_price' => null,
                    ],
                );
            }

            foreach ($data['images'] as $index => $filename) {
                $this->seedImageFile($filename);

                // Map image to variant: first N images → first N colors; extras → last color
                $variantIndex = min($index, count($data['colors']) - 1);
                $targetSku = count($data['colors']) > 1
                    ? $data['sku'].'-'.Str::of($data['colors'][$variantIndex])->slug('-')->upper()
                    : $data['sku'];
                $targetVariant = $product->variants()->where('sku', $targetSku)->first();

                $product->images()->updateOrCreate(
                    ['path' => 'products/'.$filename],
                    [
                        'product_variant_id' => $targetVariant?->id,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ],
                );
            }
        }
    }

    /**
     * Copies a versioned fixture image (committed under database/seeders/images/products/)
     * onto the public disk, so seeded ProductImage rows point at files that actually exist —
     * storage/app/public is gitignored (runtime-generated), fixture images are not.
     */
    private function seedImageFile(string $filename): void
    {
        $disk = Storage::disk('public');
        $storagePath = 'products/'.$filename;

        if ($disk->missing($storagePath)) {
            $disk->put($storagePath, File::get(database_path('seeders/images/products/'.$filename)));
        }
    }

    private function seedCategoryImageFile(string $filename): void
    {
        $disk = Storage::disk('public');
        $storagePath = 'categories/'.$filename;

        if ($disk->missing($storagePath)) {
            $disk->put($storagePath, File::get(database_path('seeders/images/categories/'.$filename)));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function products(): array
    {
        $description = implode("\n\n", [
            '100% Cuero',
            'Hecho por manos artesanas',
            'Herrajes con baño en oro 24k',
        ]);

        return [
            [
                'name' => 'Clutch Queen Bee',
                'material' => 'Cuero',
                'description' => $description,
                'dimensions' => '24cm x 12cm x 5cm',
                'sku' => 'D2300-3-2-4',
                'price' => 850_000,
                'stock' => 0,
                'colors' => ['Dorado'],
                'images' => ['clutch-queen-bee-1.png', 'clutch-queen-bee-2.png', 'clutch-queen-bee-3.png'],
            ],
            [
                'name' => 'Honey Bag Medium',
                'material' => 'Cuero',
                'description' => $description,
                'dimensions' => '36cm x 29cm x 8cm',
                'sku' => 'D2401',
                'price' => 799_000,
                'stock' => 10,
                'colors' => ['Negro', 'Rojo', 'Beige'],
                'images' => ['honey-bag-medium-1.png', 'honey-bag-medium-2.png', 'honey-bag-medium-3.png'],
            ],
            [
                'name' => 'Maxi Honey Bag',
                'material' => 'Cuero',
                'description' => $description,
                'dimensions' => '26cm x 25cm x 6cm',
                'sku' => 'D2402',
                'price' => 999_000,
                'stock' => 10,
                'colors' => ['Naranja', 'Verde', 'Gris'],
                'images' => ['maxi-honey-bag-1.png', 'maxi-honey-bag-2.png', 'maxi-honey-bag-3.png', 'maxi-honey-bag-4.png'],
            ],
            [
                'name' => 'Mini Basket Bag',
                'material' => 'Cuero',
                'description' => $description,
                'dimensions' => '15cm x 12cm x 12cm',
                'sku' => 'd1000',
                'price' => 699_000,
                'stock' => 10,
                'colors' => ['Beige', 'Verde', 'Naranja'],
                'images' => ['mini-basket-bag-1.png', 'mini-basket-bag-2.png', 'mini-basket-bag-3.png'],
            ],
            [
                'name' => 'Mini Honey Bag',
                'material' => 'Cuero',
                'description' => $description,
                'dimensions' => '27cm x 27cm x 10cm',
                'sku' => 'D2409',
                'price' => 689_000,
                'stock' => 10,
                'colors' => ['Púrpura', 'Rojo', 'Verde', 'Rojo oscuro'],
                'images' => ['mini-honey-bag-1.png', 'mini-honey-bag-2.png', 'mini-honey-bag-3.png', 'mini-honey-bag-4.png'],
            ],
        ];
    }
}
