<?php

declare(strict_types=1);

namespace Tests\Feature\Catalog;

use App\Models\ProductImage;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSeederImagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_product_images_exist_on_the_public_disk(): void
    {
        Storage::fake('public');

        $this->seed(ProductSeeder::class);

        $paths = ProductImage::query()->pluck('path');

        $this->assertNotEmpty($paths);

        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_seeding_twice_does_not_fail_and_keeps_files_present(): void
    {
        Storage::fake('public');

        $this->seed(ProductSeeder::class);
        $this->seed(ProductSeeder::class);

        $paths = ProductImage::query()->pluck('path');

        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }
}
