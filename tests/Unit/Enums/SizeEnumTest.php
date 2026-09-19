<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\Products\SizeEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SizeEnumTest extends TestCase
{
    use RefreshDatabase;

    public function test_enum_has_expected_commercial_cases(): void
    {
        $this->assertSame('mini', SizeEnum::Mini->value);
        $this->assertSame('medium', SizeEnum::Medium->value);
        $this->assertSame('maxi', SizeEnum::Maxi->value);
        $this->assertSame('one_size', SizeEnum::OneSize->value);
    }

    public function test_enum_returns_localized_labels(): void
    {
        App::setLocale('es');
        $this->assertSame('Mini', SizeEnum::Mini->label());
        $this->assertSame('Mediano', SizeEnum::Medium->label());
        $this->assertSame('Maxi', SizeEnum::Maxi->label());
        $this->assertSame('Único', SizeEnum::OneSize->label());

        App::setLocale('en');
        $this->assertSame('Mini', SizeEnum::Mini->label());
        $this->assertSame('Medium', SizeEnum::Medium->label());
        $this->assertSame('Maxi', SizeEnum::Maxi->label());
        $this->assertSame('One Size', SizeEnum::OneSize->label());
    }

    public function test_enum_sort_order_orders_from_smallest_to_largest(): void
    {
        $cases = [SizeEnum::Maxi, SizeEnum::Mini, SizeEnum::OneSize, SizeEnum::Medium];

        usort($cases, fn (SizeEnum $a, SizeEnum $b) => $a->sortOrder() <=> $b->sortOrder());

        $this->assertSame([
            SizeEnum::Mini,
            SizeEnum::Medium,
            SizeEnum::Maxi,
            SizeEnum::OneSize,
        ], $cases);
    }

    public function test_product_variant_casts_size_to_size_enum(): void
    {
        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->for($product)->create([
            'size' => SizeEnum::Medium,
        ]);

        $variant->refresh();

        $this->assertInstanceOf(SizeEnum::class, $variant->size);
        $this->assertSame(SizeEnum::Medium, $variant->size);
        $this->assertSame('medium', $variant->size->value);
    }

    public function test_product_variant_handles_null_size(): void
    {
        $product = Product::factory()->create();

        $variant = ProductVariant::factory()->for($product)->create([
            'size' => null,
        ]);

        $variant->refresh();

        $this->assertNull($variant->size);
    }
}
