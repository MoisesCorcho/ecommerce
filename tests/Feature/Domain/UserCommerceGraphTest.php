<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserCommerceGraphTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_commerce_graph_is_wired_correctly(): void
    {
        $user = User::factory()->create();

        $address = Address::factory()->for($user)->default()->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create();

        $cart = Cart::factory()->for($user)->create([
            'currency' => CurrencyEnum::Cop,
        ]);

        $cartItem = CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $review = Review::factory()
            ->for($user)
            ->for($product)
            ->approved()
            ->verifiedPurchase()
            ->create([
                'rating' => 5,
            ]);

        $wishlist = Wishlist::factory()
            ->for($user)
            ->for($product)
            ->create();

        $coupon = Coupon::factory()->percentage(10)->create();
        $order = Order::factory()->for($user)->create([
            'coupon_id' => $coupon->id,
            'currency' => CurrencyEnum::Cop,
        ]);

        $redemption = CouponRedemption::factory()->create([
            'coupon_id' => $coupon->id,
            'order_id' => $order->id,
            'user_id' => $user->id,
            'discount_amount' => 50_000,
            'currency' => CurrencyEnum::Cop,
        ]);

        $user = $user->fresh([
            'addresses',
            'cart.items.productVariant',
            'reviews.product',
            'wishlists.product',
            'orders.coupon',
            'couponRedemptions.coupon',
        ]);

        $this->assertTrue($user->addresses->contains($address));
        $this->assertTrue($user->cart->is($cart));
        $this->assertTrue($user->cart->items->contains($cartItem));
        $this->assertTrue($user->cart->items->first()->productVariant->is($variant));
        $this->assertSame(CurrencyEnum::Cop, $user->cart->currency);

        $this->assertTrue($user->reviews->contains($review));
        $this->assertTrue($user->reviews->first()->product->is($product));
        $this->assertTrue(
            Review::query()->approved()->whereKey($review->id)->exists()
        );

        $this->assertTrue($user->wishlists->contains($wishlist));
        $this->assertTrue($user->wishlists->first()->product->is($product));

        $this->assertTrue($user->orders->contains($order));
        $this->assertTrue($order->fresh()->coupon->is($coupon));
        $this->assertTrue($user->couponRedemptions->contains($redemption));
        $this->assertTrue($redemption->fresh()->coupon->is($coupon));
        $this->assertTrue($redemption->fresh()->order->is($order));
    }

    public function test_category_parent_child_graph_is_wired_correctly(): void
    {
        $parent = Category::factory()->create([
            'name' => 'Bolsos',
            'slug' => 'bolsos',
        ]);

        $child = Category::factory()->create([
            'name' => 'Mini',
            'slug' => 'mini',
            'parent_id' => $parent->id,
        ]);

        $parent = $parent->fresh(['children']);
        $child = $child->fresh(['parent']);

        $this->assertTrue($parent->children->contains($child));
        $this->assertTrue($child->parent->is($parent));
    }
}
