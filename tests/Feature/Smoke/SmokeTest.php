<?php

declare(strict_types=1);

namespace Tests\Feature\Smoke;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    private function createPublishedProduct(): Product
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'is_active' => true,
        ]);

        $variant->prices()->create([
            'currency' => CurrencyEnum::Cop,
            'price' => 150000,
        ]);

        $variant->prices()->create([
            'currency' => CurrencyEnum::Eur,
            'price' => 4500,
        ]);

        return $product;
    }

    public function test_public_pages_load_successfully(): void
    {
        $product = $this->createPublishedProduct();

        $routes = [
            '/',
            '/products',
            '/products/'.$product->slug,
            '/cart',
            '/contact',
            '/faq',
            '/about-us',
            '/login',
            '/register',
            '/forgot-password',
        ];

        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_thank_you_page_loads_with_signed_url(): void
    {
        $order = Order::factory()->create();
        $signedUrl = URL::temporarySignedRoute(
            'orders.thank-you',
            now()->addDay(),
            ['order' => $order->id]
        );

        $response = $this->get($signedUrl);
        $response->assertStatus(200);
        $response->assertSee('Gracias por su compra');
    }

    public function test_checkout_redirects_when_cart_is_empty(): void
    {
        $response = $this->get('/checkout');
        $response->assertRedirect('/cart');
    }

    public function test_protected_customer_pages_require_auth(): void
    {
        $protectedRoutes = [
            '/profile',
            '/profile/addresses',
            '/profile/orders',
            '/profile/reviews',
            '/wishlist',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_protected_customer_pages_load_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $protectedRoutes = [
            '/profile',
            '/profile/addresses',
            '/profile/orders',
            '/profile/orders/'.$order->id,
            '/profile/reviews',
            '/wishlist',
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_cart_api_endpoints(): void
    {
        $response = $this->getJson('/api/cart');
        $response->assertStatus(200);
    }

    public function test_admin_panel_access_is_restricted_and_accessible_for_admin(): void
    {
        Role::create(['name' => 'admin']);

        $adminUser = User::factory()->create(['email' => 'admin@leen.com']);
        $adminUser->assignRole('admin');

        // Unauthenticated -> redirect to admin login
        $this->get('/admin')->assertRedirect();

        // Authenticated Admin -> 200
        $this->actingAs($adminUser)->get('/admin')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/products')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/orders')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/users')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/coupons')->assertStatus(200);
        $this->actingAs($adminUser)->get('/admin/reviews')->assertStatus(200);
    }
}
