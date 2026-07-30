<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
    }

    public function test_owner_can_view_order_detail(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->paid()->for($user)->create();
        OrderItem::factory()->create(['order_id' => $order->id, 'product_name' => 'Leather Tote']);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Leather Tote');
    }

    public function test_order_detail_shows_sku_and_review_link_for_eligible_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'reviewable-bag']);
        $variant = ProductVariant::factory()->for($product)->create(['sku' => 'LHB-001-RED']);
        $order = Order::factory()->paid()->for($user)->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Reviewable Bag',
            'sku' => 'LHB-001-RED',
        ]);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertSee('LHB-001-RED')
            ->assertSee(__('account.orders.leave_review'))
            ->assertSeeHtml('href="'.route('products.show', 'reviewable-bag').'#reviews-heading"');
    }

    public function test_order_detail_hides_review_link_for_non_eligible_status(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['slug' => 'pending-bag']);
        $variant = ProductVariant::factory()->for($product)->create();
        $order = Order::factory()->for($user)->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
        ]);

        $this->actingAs($user)
            ->get(route('profile.orders.show', $order))
            ->assertOk()
            ->assertDontSee(__('account.orders.leave_review'));
    }

    public function test_foreign_order_detail_is_forbidden(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $order = Order::factory()->paid()->for($owner)->create();

        $this->actingAs($stranger)
            ->get(route('profile.orders.show', $order))
            ->assertForbidden();
    }
}
