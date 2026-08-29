<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Services\Cart\CartPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartThresholdDiscountTest extends TestCase
{
    use RefreshDatabase;

    private CartPricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = app(CartPricingService::class);
    }

    public function test_currency_enum_returns_configured_threshold_minimums(): void
    {
        $this->assertSame(1_200_000, CurrencyEnum::Cop->thresholdDiscountMinAmount());
        $this->assertSame(30_000, CurrencyEnum::Eur->thresholdDiscountMinAmount());
        $this->assertSame(32_000, CurrencyEnum::Usd->thresholdDiscountMinAmount());
    }

    public function test_currency_enum_evaluates_eligibility_correctly(): void
    {
        // EUR: threshold is 30.000 cents (300 EUR)
        $this->assertFalse(CurrencyEnum::Eur->isThresholdDiscountEligible(29_999));
        $this->assertTrue(CurrencyEnum::Eur->isThresholdDiscountEligible(30_000));
        $this->assertTrue(CurrencyEnum::Eur->isThresholdDiscountEligible(50_000));

        // COP: threshold is 1.200.000
        $this->assertFalse(CurrencyEnum::Cop->isThresholdDiscountEligible(1_199_999));
        $this->assertTrue(CurrencyEnum::Cop->isThresholdDiscountEligible(1_200_000));

        // USD: threshold is 32.000 cents (320 USD)
        $this->assertFalse(CurrencyEnum::Usd->isThresholdDiscountEligible(31_999));
        $this->assertTrue(CurrencyEnum::Usd->isThresholdDiscountEligible(32_000));
    }

    public function test_currency_enum_calculates_10_percent_discount_with_floor(): void
    {
        // 30.000 cents EUR -> 3.000 cents discount
        $this->assertSame(3_000, CurrencyEnum::Eur->calculateThresholdDiscount(30_000));

        // 35.555 cents EUR -> 3.555 cents discount
        $this->assertSame(3_555, CurrencyEnum::Eur->calculateThresholdDiscount(35_555));

        // Below threshold -> 0
        $this->assertSame(0, CurrencyEnum::Eur->calculateThresholdDiscount(29_999));
    }

    public function test_cart_pricing_service_returns_zero_discount_when_below_threshold(): void
    {
        $cart = Cart::factory()->create(['currency' => CurrencyEnum::Eur]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // Price: 100 EUR = 10.000 cents, quantity 2 = 20.000 cents (below 30.000 threshold)
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Eur,
            'price' => 10_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $view = $this->pricingService->view($cart);

        $this->assertSame(20_000, $view->subtotal);
        $this->assertSame(0, $view->thresholdDiscountAmount);
        $this->assertSame(30_000, $view->thresholdMinAmount);
        $this->assertSame(10_000, $view->remainingForThreshold);
        $this->assertFalse($view->thresholdReached);
        $this->assertSame(20_000, $view->total);
    }

    public function test_cart_pricing_service_applies_10_percent_discount_when_threshold_met_in_eur(): void
    {
        $cart = Cart::factory()->create(['currency' => CurrencyEnum::Eur]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // Price: 150 EUR = 15.000 cents, quantity 2 = 30.000 cents (meets 30.000 threshold)
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Eur,
            'price' => 15_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $view = $this->pricingService->view($cart);

        $this->assertSame(30_000, $view->subtotal);
        $this->assertSame(3_000, $view->thresholdDiscountAmount);
        $this->assertSame(30_000, $view->thresholdMinAmount);
        $this->assertSame(0, $view->remainingForThreshold);
        $this->assertTrue($view->thresholdReached);
        $this->assertSame(27_000, $view->total);
    }

    public function test_cart_pricing_service_applies_10_percent_discount_when_threshold_met_in_cop(): void
    {
        $cart = Cart::factory()->create(['currency' => CurrencyEnum::Cop]);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 10, 'is_active' => true]);

        // Price: 750.000 COP, quantity 2 = 1.500.000 COP (meets 1.200.000 threshold)
        ProductVariantPrice::factory()->create([
            'product_variant_id' => $variant->id,
            'currency' => CurrencyEnum::Cop,
            'price' => 750_000,
        ]);

        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $view = $this->pricingService->view($cart);

        $this->assertSame(1_500_000, $view->subtotal);
        $this->assertSame(150_000, $view->thresholdDiscountAmount);
        $this->assertSame(1_200_000, $view->thresholdMinAmount);
        $this->assertSame(0, $view->remainingForThreshold);
        $this->assertTrue($view->thresholdReached);
        $this->assertSame(1_350_000, $view->total);
    }
}
