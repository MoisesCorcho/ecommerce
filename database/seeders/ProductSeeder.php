<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'honeycomb'],
            ['name' => 'Honeycomb', 'sort_order' => 0],
        );

        foreach ($this->products() as $data) {
            $product = Product::query()->updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $category->id,
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'material' => $data['material'],
                    'dimensions' => $data['dimensions'],
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
            }

            foreach ($data['images'] as $index => $filename) {
                $product->images()->updateOrCreate(
                    ['path' => 'products/'.$filename],
                    [
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ],
                );
            }
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
