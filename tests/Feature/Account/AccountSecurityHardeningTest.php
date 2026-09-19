<?php

declare(strict_types=1);

namespace Tests\Feature\Account;

use App\Actions\Orders\CreateOrderFromCartAction;
use App\DTOs\Orders\CheckoutContactDTO;
use App\DTOs\Orders\CheckoutShippingDTO;
use App\DTOs\Orders\CreateOrderFromCartDTO;
use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\InvalidCheckoutAddressException;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\RoleAndAdminBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndAdminBackfillSeeder::class);
    }

    private function createOrderForUser(User $user): Order
    {
        /** @var Order $order */
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatusEnum::Pending,
            'currency' => CurrencyEnum::Cop,
            'total' => 100000,
        ]);

        return $order;
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderB = $this->createOrderForUser($userB);

        // User A attempts IDOR on User B's order page
        $response = $this->actingAs($userA)->get("/profile/orders/{$orderB->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_checkout_using_another_users_saved_address(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // User B has a saved address
        /** @var Address $addressB */
        $addressB = Address::factory()->create(['user_id' => $userB->id]);

        // User A sets up a cart
        $product = Product::factory()->create(['is_active' => true]);
        /** @var ProductVariant $variant */
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'is_active' => true,
        ]);
        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 50000,
        ]);

        $cartA = Cart::factory()->create([
            'user_id' => $userA->id,
            'currency' => CurrencyEnum::Cop,
        ]);

        CartItem::factory()->create([
            'cart_id' => $cartA->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $action = app(CreateOrderFromCartAction::class);

        // User A attempts to hijack User B's addressId in checkout DTO
        $dto = new CreateOrderFromCartDTO(
            cartId: $cartA->id,
            contact: new CheckoutContactDTO(
                firstName: 'UserA',
                lastName: 'Attacker',
                email: $userA->email,
                phone: '+573000000000',
            ),
            shipping: new CheckoutShippingDTO(
                fullName: 'User A',
                phone: '+573000000000',
                addressLine1: 'Fake Street 123',
                addressLine2: null,
                city: 'Bogotá',
                state: 'Cundinamarca',
                country: 'CO',
                postalCode: '110111',
                addressId: $addressB->id, // IDOR attempt!
            ),
            userId: $userA->id,
        );

        $this->expectException(InvalidCheckoutAddressException::class);
        $action($dto);
    }

    public function test_user_cannot_view_thank_you_page_for_another_users_order(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderB = $this->createOrderForUser($userB);

        // User A attempts to view thank-you page for User B's order without signed URL or ownership
        $response = $this->actingAs($userA)->get("/orders/{$orderB->id}/thank-you");

        $response->assertStatus(403);
    }

    public function test_customer_role_cannot_access_filament_admin_resources(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');

        $adminRoutes = [
            '/admin',
            '/admin/products',
            '/admin/orders',
            '/admin/coupons',
            '/admin/users',
            '/admin/categories',
            '/admin/reviews',
        ];

        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($customer)->get($route);
            $response->assertStatus(403);
        }
    }
}
