<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Enums\Commerce\CurrencyEnum;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Dusk\Browser;
use Spatie\Permission\Models\Role;
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

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminUser = User::factory()->create();
        $adminUser->assignRole($adminRole);

        $this->browse(function (Browser $browser) use ($activeProduct, $outOfStockProduct, $customerUser, $customerOrder, $adminUser): void {
            // === BLOQUE 1: Experiencia Pública y Marca ===

            // VIS-01: Home Page
            $browser->visit('/')
                ->assertPathIs('/')
                ->screenshot('01-home-page');

            // VIS-02: Quienes Somos (About Us)
            $browser->visit('/about-us')
                ->assertPathIs('/about-us')
                ->screenshot('02-about-us-page');

            // VIS-03: Preguntas Frecuentes (FAQ)
            $browser->visit('/faq')
                ->assertPathIs('/faq')
                ->screenshot('03-faq-page');

            // VIS-04: Contacto
            $browser->visit('/contact')
                ->assertPathIs('/contact')
                ->screenshot('04-contact-page');

            // === BLOQUE 2: Catálogo y Flujo de Compra ===

            // VIS-05: Catálogo de Productos
            $browser->visit('/products')
                ->assertPathIs('/products')
                ->screenshot('05-catalog-page');

            // VIS-06: Detalle de Producto con insignia Agotado (PDP)
            $browser->visit('/products/'.$outOfStockProduct->slug)
                ->assertPathIs('/products/'.$outOfStockProduct->slug)
                ->screenshot('06-product-detail');

            // Flujo de Carrito y Checkout: Agregar producto al carrito desde la PDP activa
            $browser->visit('/products/'.$activeProduct->slug)
                ->waitFor('[data-add-to-cart]')
                ->click('[data-add-to-cart]')
                ->pause(1500);

            // VIS-07: Carrito de Compras (con ítem en carrito)
            $browser->visit('/cart')
                ->assertPathIs('/cart')
                ->screenshot('07-cart-page');

            // VIS-08: Formulario de Checkout (con ítem en carrito)
            $browser->visit('/checkout')
                ->assertPathIs('/checkout')
                ->screenshot('08-checkout-page');

            // VIS-09: Página "Gracias por su compra" (Autenticado / Dueño de la orden)
            $browser->loginAs($customerUser)
                ->visit('/orders/'.$customerOrder->id.'/thank-you')
                ->assertPathIs('/orders/'.$customerOrder->id.'/thank-you')
                ->screenshot('09-thank-you-page');

            // === BLOQUE 3: Acceso / Autenticación de Cliente ===

            // VIS-10: Login Cliente (Invitado)
            $browser->logout()
                ->visit('/login')
                ->assertPathIs('/login')
                ->screenshot('10-login-page');

            // VIS-11: Registro Cliente
            $browser->visit('/register')
                ->assertPathIs('/register')
                ->screenshot('11-register-page');

            // === BLOQUE 4: Área / Perfil de Cuenta del Comprador (Seguidilla) ===

            // VIS-12: Perfil Cliente (Datos)
            $browser->loginAs($customerUser)
                ->visit('/profile')
                ->assertPathIs('/profile')
                ->screenshot('12-profile-dashboard-page');

            // VIS-13: Mis Direcciones
            $browser->visit('/profile/addresses')
                ->assertPathIs('/profile/addresses')
                ->screenshot('13-profile-addresses-page');

            // VIS-14: Mis Pedidos
            $browser->visit('/profile/orders')
                ->assertPathIs('/profile/orders')
                ->screenshot('14-profile-orders-page');

            // VIS-15: Mis Reseñas
            $browser->visit('/profile/reviews')
                ->assertPathIs('/profile/reviews')
                ->screenshot('15-profile-reviews-page');

            // VIS-16: Lista de Deseos (Wishlist)
            $browser->visit('/wishlist')
                ->assertPathIs('/wishlist')
                ->screenshot('16-profile-wishlist-page');

            // === BLOQUE 5: Administración (Panel de Filament) ===

            // VIS-17: Login Panel de Administración (Invitado)
            $browser->logout()
                ->visit('/admin/login')
                ->assertPathIs('/admin/login')
                ->screenshot('17-admin-login-page');

            // VIS-18: Dashboard de Admin (Autenticado como Administrador)
            $browser->loginAs($adminUser)
                ->visit('/admin')
                ->assertPathIs('/admin')
                ->screenshot('18-admin-dashboard-page');

            // VIS-19: Gestión de Productos en Admin
            $browser->visit('/admin/products')
                ->assertPathIs('/admin/products')
                ->screenshot('19-admin-products-page');

            // VIS-20: Gestión de Categorías en Admin
            $browser->visit('/admin/categories')
                ->assertPathIs('/admin/categories')
                ->screenshot('20-admin-categories-page');

            // VIS-21: Gestión de Pedidos en Admin
            $browser->visit('/admin/orders')
                ->assertPathIs('/admin/orders')
                ->screenshot('21-admin-orders-page');

            // VIS-22: Gestión de Cupones en Admin
            $browser->visit('/admin/coupons')
                ->assertPathIs('/admin/coupons')
                ->screenshot('22-admin-coupons-page');

            // VIS-23: Gestión de Usuarios en Admin
            $browser->visit('/admin/users')
                ->assertPathIs('/admin/users')
                ->screenshot('23-admin-users-page');

            // VIS-24: Gestión de Reseñas en Admin
            $browser->visit('/admin/reviews')
                ->assertPathIs('/admin/reviews')
                ->screenshot('24-admin-reviews-page');
        });
    }
}
