<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class SmokeTest extends DuskTestCase
{
    public function test_visual_smoke_checklist(): void
    {
        $products = Product::query()->publishedForStorefront(CurrencyEnum::Cop)->take(2)->get();

        if ($products->count() < 2) {
            $this->seed(DatabaseSeeder::class);
            $products = Product::query()->publishedForStorefront(CurrencyEnum::Cop)->take(2)->get();
        }

        $activeProduct = $products->first();
        // Ensure active product has stock for cart/checkout flow
        $activeProduct->variants()->update(['stock' => 10, 'is_active' => true]);

        $outOfStockProduct = $products->last();
        // Ensure out of stock product has 0 stock for PDP badge test
        $outOfStockProduct->variants()->update(['stock' => 0]);

        $customerUser = User::factory()->create();
        $customerOrder = Order::factory()->create([
            'user_id' => $customerUser->id,
            'currency' => CurrencyEnum::Cop,
            'total' => 799000,
        ]);

        $this->browse(function (Browser $browser) use ($activeProduct, $outOfStockProduct, $customerUser, $customerOrder): void {
            // VIS-01: Home Page
            $browser->visit('/')
                ->assertPathIs('/')
                ->screenshot('01-home-page');

            // VIS-02: Catálogo de Productos
            $browser->visit('/products')
                ->assertPathIs('/products')
                ->screenshot('02-catalog-page');

            // VIS-03: Detalle de Producto con insginia Agotado (PDP)
            $browser->visit('/products/'.$outOfStockProduct->slug)
                ->assertPathIs('/products/'.$outOfStockProduct->slug)
                ->screenshot('03-product-detail');

            // Flujo de Carrito y Checkout: Agregar producto al carrito desde la PDP activa
            $browser->visit('/products/'.$activeProduct->slug)
                ->waitFor('[data-add-to-cart]')
                ->click('[data-add-to-cart]')
                ->pause(1500);

            // VIS-04: Carrito de Compras (con ítem en carrito)
            $browser->visit('/cart')
                ->assertPathIs('/cart')
                ->screenshot('04-cart-page');

            // VIS-05: Formulario de Checkout (con ítem en carrito)
            $browser->visit('/checkout')
                ->assertPathIs('/checkout')
                ->screenshot('05-checkout-page');

            // VIS-06: Página "Gracias por su compra" (Autenticado / Dueño de la orden)
            $browser->loginAs($customerUser)
                ->visit('/orders/'.$customerOrder->id.'/thank-you')
                ->assertPathIs('/orders/'.$customerOrder->id.'/thank-you')
                ->screenshot('06-thank-you-page');

            // VIS-07: Quienes Somos (About Us)
            $browser->visit('/about-us')
                ->assertPathIs('/about-us')
                ->screenshot('07-about-us-page');

            // VIS-08: Preguntas Frecuentes (FAQ)
            $browser->visit('/faq')
                ->assertPathIs('/faq')
                ->screenshot('08-faq-page');

            // VIS-09: Contacto
            $browser->visit('/contact')
                ->assertPathIs('/contact')
                ->screenshot('09-contact-page');

            // VIS-10: Login Cliente (Invitado)
            $browser->logout()
                ->visit('/login')
                ->assertPathIs('/login')
                ->screenshot('10-login-page');

            // VIS-11: Registro Cliente
            $browser->visit('/register')
                ->assertPathIs('/register')
                ->screenshot('11-register-page');

            // VIS-12: Perfil Cliente (Autenticado)
            $browser->loginAs($customerUser)
                ->visit('/profile')
                ->assertPathIs('/profile')
                ->screenshot('12-customer-profile-page');

            // VIS-13: Mis Pedidos (Autenticado)
            $browser->visit('/profile/orders')
                ->assertPathIs('/profile/orders')
                ->screenshot('13-customer-orders-page');

            // VIS-14: Lista de Deseos (Autenticado)
            $browser->visit('/wishlist')
                ->assertPathIs('/wishlist')
                ->screenshot('14-customer-wishlist-page');

            // VIS-15: Login Panel de Administración (Filament)
            $browser->logout()
                ->visit('/admin/login')
                ->assertPathIs('/admin/login')
                ->screenshot('15-admin-login-page');
        });
    }
}
