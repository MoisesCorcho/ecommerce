<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_color_can_be_created_and_auto_generates_slug(): void
    {
        $color = Color::create([
            'name' => 'Verde Esmeralda',
            'hex_code' => '#50C878',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->assertSame('verde-esmeralda', $color->slug);
        $this->assertSame('#50C878', $color->hex_code);
        $this->assertTrue($color->is_active);
    }

    public function test_active_scope_filters_inactive_colors(): void
    {
        Color::factory()->create(['name' => 'Color Inactivo', 'is_active' => false]);
        $active = Color::factory()->create(['name' => 'Color Activo', 'is_active' => true]);

        $activeColors = Color::query()->active()->get();

        $this->assertCount(1, $activeColors);
        $this->assertTrue($activeColors->first()->is($active));
    }

    public function test_color_has_many_variants_relation(): void
    {
        $color = Color::factory()->create(['name' => 'Miel Intenso', 'hex_code' => '#C5832A']);
        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->for($product)->create([
            'color_id' => $color->id,
            'sku' => 'MIEL-01',
        ]);

        $this->assertTrue($color->variants->contains($variant));
        $this->assertTrue($variant->colorModel->is($color));
        $this->assertSame('Miel Intenso', $variant->color);
        $this->assertSame('#C5832A', $variant->color_hex);
    }
}
