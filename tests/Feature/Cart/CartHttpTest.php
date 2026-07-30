<?php

declare(strict_types=1);

namespace Tests\Feature\Cart;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Cart\CartSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_happy_path_add_update_remove_and_show(): void
    {
        $variant = $this->createEligibleVariant(stock: 10, copPrice: 12_000);

        $add = $this->postJson(route('cart.items.store'), [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $add->assertCreated()
            ->assertJsonPath('currency', CurrencyEnum::Cop->value)
            ->assertJsonPath('total', 24_000)
            ->assertJsonPath('lines.0.product_variant_id', $variant->id)
            ->assertJsonPath('lines.0.quantity', 2)
            ->assertJsonPath('lines.0.unit_price', 12_000);

        $this->assertNotNull(session(CartSession::KEY));

        $update = $this->patchJson(route('cart.items.update', $variant->id), [
            'quantity' => 3,
        ]);

        $update->assertOk()
            ->assertJsonPath('lines.0.quantity', 3)
            ->assertJsonPath('total', 36_000);

        $show = $this->getJson(route('cart.show'));
        $show->assertOk()
            ->assertJsonPath('total', 36_000);

        $remove = $this->deleteJson(route('cart.items.destroy', $variant->id));
        $remove->assertOk()
            ->assertJsonPath('lines', [])
            ->assertJsonPath('total', 0);
    }

    public function test_authenticated_user_cart_and_validation_errors(): void
    {
        $user = User::factory()->create();
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 8_000);

        $this->actingAs($user)
            ->postJson(route('cart.items.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertCreated()
            ->assertJsonPath('total', 8_000);

        $this->actingAs($user)
            ->postJson(route('cart.items.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['quantity']);

        $this->actingAs($user)
            ->postJson(route('cart.currency'), [
                'currency' => 'USD',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['currency']);
    }

    private function createEligibleVariant(int $stock, int $copPrice): ProductVariant
    {
        $product = Product::factory()->create(['is_active' => true]);
        $variant = ProductVariant::factory()->for($product)->create([
            'is_active' => true,
            'stock' => $stock,
        ]);

        ProductVariantPrice::factory()
            ->for($variant, 'productVariant')
            ->cop()
            ->create(['price' => $copPrice]);

        return $variant;
    }
}
