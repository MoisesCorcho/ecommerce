<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use App\Support\Cart\CartSession;
use Database\Seeders\RoleAndAdminBackfillSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        $this->seed(RoleAndAdminBackfillSeeder::class);
    }

    public function test_valid_credentials_log_in_and_merge_guest_cart(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);
        $user->assignRole('customer');

        $variant = $this->createEligibleVariant(stock: 5, copPrice: 25_000);

        $sessionId = 'guest-session-login-test';
        CartSession::setId($sessionId);
        $guestCart = Cart::factory()->guest()->create(['session_id' => $sessionId]);
        CartItem::factory()->for($guestCart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        Livewire::test('login-page')
            ->set('email', $user->email)
            ->set('password', 'Password123!')
            ->call('login')
            ->assertRedirect(route('home'));

        $this->assertTrue(Auth::check());
        $this->assertSame($user->id, Auth::id());
        $this->assertDatabaseMissing('carts', ['id' => $guestCart->id]);

        $userCart = Cart::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $userCart->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
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

    public function test_invalid_credentials_are_rejected_with_generic_message(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $component = Livewire::test('login-page')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');

        $this->assertFalse(Auth::check());
        $component->assertSet('errorMessage', __('auth.failed'));
    }

    public function test_excessive_attempts_are_throttled(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        for ($i = 0; $i < 5; $i++) {
            Livewire::test('login-page')
                ->set('email', $user->email)
                ->set('password', 'wrong-password')
                ->call('login');
        }

        $component = Livewire::test('login-page')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');

        $this->assertFalse(Auth::check());
        $this->assertStringContainsString('Too many', (string) $component->get('errorMessage'));
    }
}
