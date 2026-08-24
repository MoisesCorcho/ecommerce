<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\ProductVariantPrice;
use Tests\TestCase;

class ProductVariantPriceTest extends TestCase
{
    public function test_has_discount_returns_true_only_when_compare_at_price_is_greater_than_price(): void
    {
        $discountedPrice = new ProductVariantPrice([
            'currency' => CurrencyEnum::Cop,
            'price' => 800_000,
            'compare_at_price' => 1_000_000,
        ]);
        $this->assertTrue($discountedPrice->hasDiscount());

        $regularPrice = new ProductVariantPrice([
            'currency' => CurrencyEnum::Usd,
            'price' => 200,
            'compare_at_price' => null,
        ]);
        $this->assertFalse($regularPrice->hasDiscount());

        $equalPrice = new ProductVariantPrice([
            'currency' => CurrencyEnum::Eur,
            'price' => 200,
            'compare_at_price' => 200,
        ]);
        $this->assertFalse($equalPrice->hasDiscount());

        $invertedPrice = new ProductVariantPrice([
            'currency' => CurrencyEnum::Cop,
            'price' => 300_000,
            'compare_at_price' => 250_000,
        ]);
        $this->assertFalse($invertedPrice->hasDiscount());
    }

    public function test_discount_percentage_calculates_integer_percentage_correctly(): void
    {
        // 20% discount (1,000,000 down to 800,000)
        $price20 = new ProductVariantPrice([
            'currency' => CurrencyEnum::Cop,
            'price' => 800_000,
            'compare_at_price' => 1_000_000,
        ]);
        $this->assertSame(20, $price20->discountPercentage());

        // 33% discount (300 down to 200)
        $price33 = new ProductVariantPrice([
            'currency' => CurrencyEnum::Eur,
            'price' => 200,
            'compare_at_price' => 300,
        ]);
        $this->assertSame(33, $price33->discountPercentage());

        // No discount -> null
        $regularPrice = new ProductVariantPrice([
            'currency' => CurrencyEnum::Usd,
            'price' => 200,
            'compare_at_price' => null,
        ]);
        $this->assertNull($regularPrice->discountPercentage());
    }
}
