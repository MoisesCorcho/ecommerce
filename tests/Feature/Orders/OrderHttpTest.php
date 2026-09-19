<?php

declare(strict_types=1);

namespace Tests\Feature\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Cart\CartSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class OrderHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_redirects_to_cart_when_empty(): void
    {
        $this->get(route('checkout.show'))
            ->assertRedirect(route('cart.page'));
    }

    public function test_guest_can_place_order_via_checkout_and_open_signed_thank_you(): void
    {
        $sessionId = 'guest-checkout-session';
        CartSession::setId($sessionId);

        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 25_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->assertSet('preview.subtotal', 25_000)
            ->set('firstName', 'Grace')
            ->set('lastName', 'Hopper')
            ->set('email', 'grace@example.com')
            ->set('phone', '+573001234567')
            ->set('addressMode', 'one_shot')
            ->set('shippingFullName', 'Grace Hopper')
            ->set('shippingPhone', '+573001234567')
            ->set('shippingAddressLine1', 'Av 1 #2-3')
            ->set('shippingCity', 'Cali')
            ->set('shippingState', 'Valle')
            ->set('shippingCountry', 'CO')
            ->set('customerNotes', 'Timbre roto')
            ->call('confirm')
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatusEnum::Pending, $order->status);
        $this->assertSame('grace@example.com', $order->email);
        $this->assertNull($order->user_id);
        $this->assertSame('Timbre roto', $order->customer_notes);
        $this->assertSame(0, CartItem::query()->where('cart_id', $cart->id)->count());
        $this->assertSame(5, (int) $variant->fresh()->stock);

        $this->get(route('orders.thank-you', $order))
            ->assertForbidden();

        $signed = URL::temporarySignedRoute(
            'orders.thank-you',
            now()->addDay(),
            ['order' => $order->id],
        );

        $this->get($signed)
            ->assertOk()
            ->assertSee($order->order_number, false)
            ->assertSee(__('orders.thank_you.title'), false);
    }

    public function test_user_can_view_own_thank_you_without_signature(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
        ]);

        $this->actingAs($user)
            ->get(route('orders.thank-you', $order))
            ->assertOk()
            ->assertSee($order->order_number, false);
    }

    public function test_user_cannot_view_foreign_order_thank_you(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other)
            ->get(route('orders.thank-you', $order))
            ->assertForbidden();
    }

    public function test_checkout_validates_required_contact_fields(): void
    {
        $sessionId = 'guest-validation-session';
        CartSession::setId($sessionId);
        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 3, copPrice: 10_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::test('checkout-page')
            ->set('firstName', '')
            ->set('email', 'not-an-email')
            ->call('confirm')
            ->assertHasErrors(['firstName', 'email']);
    }

    public function test_selecting_saved_address_radio_populates_shipping_fields(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->for($user)->create([
            'full_name' => 'Ada Lovelace',
            'phone' => '+573009998888',
            'address_line_1' => 'Cra 7 #45-12',
            'address_line_2' => 'Apto 301',
            'city' => 'Bogotá',
            'state' => 'Cundinamarca',
            'country' => 'CO',
            'postal_code' => '110111',
        ]);

        $cart = Cart::factory()->for($user)->create();
        $variant = $this->createEligibleVariant(stock: 4, copPrice: 30_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        Livewire::actingAs($user)
            ->test('checkout-page')
            ->assertSet('addressMode', 'saved')
            ->set('shippingAddressId', $address->id)
            ->assertSet('shippingFullName', 'Ada Lovelace')
            ->assertSet('shippingPhone', '+573009998888')
            ->assertSet('shippingAddressLine1', 'Cra 7 #45-12')
            ->assertSet('shippingAddressLine2', 'Apto 301')
            ->assertSet('shippingCity', 'Bogotá')
            ->assertSet('shippingState', 'Cundinamarca')
            ->assertSet('shippingCountry', 'CO')
            ->assertSet('shippingPostalCode', '110111');
    }

    public function test_checkout_page_renders_new_copy_in_both_locales(): void
    {
        $sessionId = 'guest-locale-session';
        CartSession::setId($sessionId);
        $cart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        $variant = $this->createEligibleVariant(stock: 2, copPrice: 15_000);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        App::setLocale('en');
        $this->get(route('checkout.show'))
            ->assertOk()
            ->assertSee(__('orders.checkout.breadcrumb_checkout'))
            ->assertSee(__('orders.checkout.back_to_cart'))
            ->assertSee(__('orders.checkout.summary_title'))
            ->assertSee(__('orders.checkout.stepper.payment_hint'));

        App::setLocale('es');
        $this->get(route('checkout.show'))
            ->assertOk()
            ->assertSee(__('orders.checkout.breadcrumb_checkout'))
            ->assertSee(__('orders.checkout.back_to_cart'))
            ->assertSee(__('orders.checkout.summary_title'))
            ->assertSee(__('orders.checkout.stepper.payment_hint'));

        App::setLocale('en');
    }

    public function test_guest_can_view_thank_you_when_gateway_appends_tracking_query_params(): void
    {
        $order = Order::factory()->create([
            'user_id' => null,
            'status' => OrderStatusEnum::Pending,
        ]);

        $signed = URL::temporarySignedRoute(
            'orders.thank-you',
            now()->addDay(),
            ['order' => $order->id, 'payment' => 'processing'],
        );

        // Simulate Bold returning with gateway query params appended
        $withGatewayParams = $signed.'&bold-order-id=TX12345&status=approved&utm_source=bold';

        $this->get($withGatewayParams)
            ->assertOk()
            ->assertSee($order->order_number, false);
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
