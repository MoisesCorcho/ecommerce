<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Asserts the actual rendered message in the "es" locale, not just that
 * the translation file exists.
 */
class AuthSpanishLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('es');
    }

    public function test_login_failed_credentials_message_is_in_spanish(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);

        $component = Livewire::test('login-page')
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');

        $component->assertSet('errorMessage', 'Estas credenciales no coinciden con nuestros registros.');
    }

    public function test_login_throttle_message_is_in_spanish_with_seconds_interpolated(): void
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

        $message = (string) $component->get('errorMessage');
        $this->assertStringContainsString('Demasiados intentos de inicio de sesión', $message);
        $this->assertStringNotContainsString(':seconds', $message);
        $this->assertMatchesRegularExpression('/\d+ segundos/', $message);
    }

    public function test_password_reset_invalid_token_message_is_in_spanish(): void
    {
        $user = User::factory()->create(['password' => Hash::make('OldPassword123!')]);

        $component = Livewire::test('reset-password-page', ['token' => 'not-a-real-token'])
            ->set('email', $user->email)
            ->set('password', 'NewPassword123!')
            ->set('password_confirmation', 'NewPassword123!')
            ->call('resetPassword');

        $component->assertSet('errorMessage', 'Este enlace de restablecimiento de contraseña no es válido.');
    }

    public function test_password_reset_sent_message_is_in_spanish(): void
    {
        $user = User::factory()->create();
        Password::shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);

        $component = Livewire::test('forgot-password-page')
            ->set('email', $user->email)
            ->call('sendResetLink');

        $component->assertSet('statusMessage', 'Te enviamos por email el enlace para restablecer tu contraseña.');
    }

    public function test_checkout_verify_email_required_message_is_in_spanish(): void
    {
        $user = User::factory()->unverified()->create();
        $variant = $this->createEligibleVariant(stock: 5, copPrice: 25_000);

        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->for($cart)->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user);

        Livewire::test('checkout-page')
            ->call('confirm')
            ->assertSet('errorMessage', 'Verifica tu email para continuar.');
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
