<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Products\SizeEnum;
use App\Mail\Wishlist\WishlistLowStockMail;
use App\Mail\Wishlist\WishlistPriceDropMail;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class WishlistMailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_wishlist_price_drop_mail_renders_correctly(): void
    {
        App::setLocale('es');
        $user = User::factory()->create(['name' => 'Valentina']);
        $product = Product::factory()->create(['name' => 'Bolso Miel']);
        $variant = ProductVariant::factory()->for($product)->create(['size' => SizeEnum::Medium]);
        ProductImage::factory()->for($variant)->create(['path' => 'products/miel-1.jpg', 'is_primary' => true]);

        $mail = new WishlistPriceDropMail(
            user: $user,
            variant: $variant,
            oldPrice: 350_000,
            newPrice: 280_000,
            currency: CurrencyEnum::Cop,
        );

        $mail->assertHasSubject('¡Buenas noticias! Bolso Miel ha bajado de precio');

        $rendered = $mail->render();

        $this->assertStringContainsString('Bolso Miel', $rendered);
        $this->assertStringContainsString('COP$ 350.000', $rendered);
        $this->assertStringContainsString('COP$ 280.000', $rendered);
        $this->assertStringContainsString('Ver en la tienda y comprar', $rendered);
        $this->assertStringContainsString('leen-brown.png', $rendered);
    }

    public function test_wishlist_low_stock_mail_renders_correctly(): void
    {
        App::setLocale('es');
        $user = User::factory()->create(['name' => 'Camila']);
        $product = Product::factory()->create(['name' => 'Cofre Canela']);
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 2]);

        $mail = new WishlistLowStockMail(
            user: $user,
            variant: $variant,
            stockRemaining: 2,
            currency: CurrencyEnum::Eur,
        );

        $mail->assertHasSubject('¡Últimas unidades disponibles de Cofre Canela!');

        $rendered = $mail->render();

        $this->assertStringContainsString('Cofre Canela', $rendered);
        $this->assertStringContainsString('2', $rendered);
        $this->assertStringContainsString('Comprar ahora antes de que se agote', $rendered);
        $this->assertStringContainsString('leen-brown.png', $rendered);
    }

    public function test_wishlist_mails_support_english_locale(): void
    {
        App::setLocale('en');
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => 'Honey Bag']);
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 1]);

        $priceDropMail = new WishlistPriceDropMail(
            user: $user,
            variant: $variant,
            oldPrice: 25_000,
            newPrice: 19_000,
            currency: CurrencyEnum::Eur,
        );
        $priceDropMail->assertHasSubject('Good news! Honey Bag is now on sale');
        $this->assertStringContainsString('Shop now in store', $priceDropMail->render());

        $lowStockMail = new WishlistLowStockMail(
            user: $user,
            variant: $variant,
            stockRemaining: 1,
            currency: CurrencyEnum::Eur,
        );
        $lowStockMail->assertHasSubject('Only a few left of Honey Bag!');
        $this->assertStringContainsString('Buy now before it sells out', $lowStockMail->render());
    }
}
