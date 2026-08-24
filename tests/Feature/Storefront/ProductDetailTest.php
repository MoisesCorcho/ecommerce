<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_selecting_a_color_resolves_the_size_that_actually_exists_for_it(): void
    {
        $product = $this->createProductWithVariants('cross-match', [
            ['sku' => 'CM-NEGRO-36', 'color' => 'Negro', 'size' => '36cm x 29cm x 8cm'],
            ['sku' => 'CM-ROJO-36', 'color' => 'Rojo', 'size' => '36cm x 29cm x 8cm'],
            ['sku' => 'CM-AMARILLO-90', 'color' => 'Amarillo', 'size' => '90cm x 30cm x 12cm'],
        ]);

        $amarilloVariant = $product->variants->firstWhere('color', 'Amarillo');
        $this->assertNotNull($amarilloVariant);

        Livewire::test('product-detail', ['slug' => 'cross-match'])
            ->assertSet('selectedColor', 'Negro')
            ->assertSet('selectedSize', '36cm x 29cm x 8cm')
            ->set('selectedColor', 'Amarillo')
            ->assertSet('selectedSize', '90cm x 30cm x 12cm')
            ->assertSet('selectedVariantId', $amarilloVariant->id);
    }

    public function test_selecting_a_size_resolves_the_color_that_actually_exists_for_it(): void
    {
        $product = $this->createProductWithVariants('cross-match-size', [
            ['sku' => 'CMS-NEGRO-36', 'color' => 'Negro', 'size' => '36cm x 29cm x 8cm'],
            ['sku' => 'CMS-ROJO-36', 'color' => 'Rojo', 'size' => '36cm x 29cm x 8cm'],
            ['sku' => 'CMS-AMARILLO-90', 'color' => 'Amarillo', 'size' => '90cm x 30cm x 12cm'],
        ]);

        $amarilloVariant = $product->variants->firstWhere('color', 'Amarillo');
        $this->assertNotNull($amarilloVariant);

        $component = Livewire::test('product-detail', ['slug' => 'cross-match-size'])
            ->call('selectVariant', $amarilloVariant->id)
            ->assertSet('selectedColor', 'Amarillo')
            ->assertSet('selectedSize', '90cm x 30cm x 12cm')
            ->set('selectedSize', '36cm x 29cm x 8cm');

        $resolvedColor = $component->get('selectedColor');
        $this->assertContains($resolvedColor, ['Negro', 'Rojo']);
        $this->assertNotSame('Amarillo', $resolvedColor);

        $variantId = $component->get('selectedVariantId');
        $this->assertNotNull($variantId);

        $resolvedVariant = $product->variants->firstWhere('id', $variantId);
        $this->assertNotNull($resolvedVariant);
        $this->assertSame('36cm x 29cm x 8cm', $resolvedVariant->size);
        $this->assertSame($resolvedColor, $resolvedVariant->color);
    }

    public function test_selecting_a_color_keeps_the_shared_size_when_every_color_has_the_same_size(): void
    {
        $product = $this->createProductWithVariants('shared-size', [
            ['sku' => 'SS-NEGRO', 'color' => 'Negro', 'size' => 'Única'],
            ['sku' => 'SS-ROJO', 'color' => 'Rojo', 'size' => 'Única'],
        ]);

        $rojoVariant = $product->variants->firstWhere('color', 'Rojo');
        $this->assertNotNull($rojoVariant);

        Livewire::test('product-detail', ['slug' => 'shared-size'])
            ->assertSet('selectedColor', 'Negro')
            ->assertSet('selectedSize', 'Única')
            ->set('selectedColor', 'Rojo')
            ->assertSet('selectedSize', 'Única')
            ->assertSet('selectedVariantId', $rojoVariant->id);
    }

    public function test_product_detail_renders_variant_dimensions_in_specs_section(): void
    {
        $product = Product::factory()->create([
            'name' => 'Dimension Bag',
            'slug' => 'dimension-bag',
            'description' => 'Bolso con dimensiones específicas',
            'is_active' => true,
        ]);

        $miniVariant = ProductVariant::factory()->for($product)->create([
            'sku' => 'DIM-MINI',
            'color' => 'Miel',
            'size' => 'Mini',
            'dimensions' => '20cm x 15cm x 5cm',
            'stock' => 10,
            'is_active' => true,
        ]);
        ProductVariantPrice::factory()->for($miniVariant, 'productVariant')->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 200_000,
        ]);

        $maxiVariant = ProductVariant::factory()->for($product)->create([
            'sku' => 'DIM-MAXI',
            'color' => 'Miel',
            'size' => 'Maxi',
            'dimensions' => '35cm x 25cm x 10cm',
            'stock' => 10,
            'is_active' => true,
        ]);
        ProductVariantPrice::factory()->for($maxiVariant, 'productVariant')->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 350_000,
        ]);

        Livewire::test('product-detail', ['slug' => 'dimension-bag'])
            ->assertSet('selectedSize', 'Mini')
            ->assertSee('20cm x 15cm x 5cm')
            ->assertDontSee('35cm x 25cm x 10cm')
            ->set('selectedSize', 'Maxi')
            ->assertSee('35cm x 25cm x 10cm')
            ->assertDontSee('20cm x 15cm x 5cm');
    }

    /**
     * @param  array<int, array{sku: string, color: string, size: string}>  $variants
     */
    private function createProductWithVariants(string $slug, array $variants): Product
    {
        $product = Product::factory()->create([
            'name' => 'Producto '.$slug,
            'slug' => $slug,
            'is_active' => true,
        ]);

        foreach ($variants as $definition) {
            $variant = ProductVariant::factory()->for($product)->create([
                'sku' => $definition['sku'],
                'color' => $definition['color'],
                'size' => $definition['size'],
                'is_active' => true,
                'stock' => 10,
            ]);

            ProductVariantPrice::factory()
                ->for($variant, 'productVariant')
                ->create([
                    'currency' => CurrencyEnum::Cop,
                    'price' => 100_000,
                ]);
        }

        return $product->fresh(['variants.prices']) ?? $product;
    }
}
