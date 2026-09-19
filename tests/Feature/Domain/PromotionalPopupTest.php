<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Models\Coupon;
use App\Models\PromotionalPopup;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PromotionalPopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_promotional_popup_with_translatable_fields(): void
    {
        $popup = PromotionalPopup::create([
            'title' => [
                'es' => '¡Oferta Especial de Primavera!',
                'en' => 'Special Spring Offer!',
            ],
            'subtitle' => [
                'es' => 'Descuento exclusivo en toda la tienda',
                'en' => 'Exclusive discount storewide',
            ],
            'image_path' => 'popups/spring-sale.webp',
            'cta_text' => [
                'es' => 'Aprovechar Oferta',
                'en' => 'Claim Offer',
            ],
            'cta_url' => 'https://leen.com.co/shop',
            'delay_seconds' => 5,
            'is_active' => true,
            'sort_order' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
        ]);

        $this->assertDatabaseHas('promotional_popups', [
            'id' => $popup->id,
            'image_path' => 'popups/spring-sale.webp',
            'cta_url' => 'https://leen.com.co/shop',
            'delay_seconds' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame('¡Oferta Especial de Primavera!', $popup->getTranslation('title', 'es'));
        $this->assertSame('Special Spring Offer!', $popup->getTranslation('title', 'en'));
        $this->assertSame('Descuento exclusivo en toda la tienda', $popup->getTranslation('subtitle', 'es'));
        $this->assertSame('Exclusive discount storewide', $popup->getTranslation('subtitle', 'en'));
        $this->assertSame('Aprovechar Oferta', $popup->getTranslation('cta_text', 'es'));
        $this->assertSame('Claim Offer', $popup->getTranslation('cta_text', 'en'));
    }

    public function test_it_casts_attributes_correctly(): void
    {
        $popup = PromotionalPopup::create([
            'title' => ['es' => 'Título de prueba'],
            'delay_seconds' => '8',
            'is_active' => 1,
            'sort_order' => '10',
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-31 23:59:59',
        ]);

        $this->assertIsBool($popup->is_active);
        $this->assertTrue($popup->is_active);
        $this->assertIsInt($popup->sort_order);
        $this->assertSame(10, $popup->sort_order);
        $this->assertIsInt($popup->delay_seconds);
        $this->assertSame(8, $popup->delay_seconds);
        $this->assertInstanceOf(Carbon::class, $popup->starts_at);
        $this->assertInstanceOf(Carbon::class, $popup->ends_at);
    }

    public function test_scope_active_filters_by_is_active_flag(): void
    {
        $active = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up activo'],
            'is_active' => true,
        ]);

        $inactive = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up inactivo'],
            'is_active' => false,
        ]);

        $results = PromotionalPopup::query()->active()->get();

        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    public function test_scope_active_filters_by_starts_at_and_ends_at(): void
    {
        // 1. Without dates: active
        $alwaysActive = PromotionalPopup::create([
            'title' => ['es' => 'Siempre activo'],
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        // 2. Currently within date window: active
        $currentlyActive = PromotionalPopup::create([
            'title' => ['es' => 'Vigente ahora'],
            'is_active' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);

        // 3. Future start date: excluded
        $futurePopup = PromotionalPopup::create([
            'title' => ['es' => 'A futuro'],
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(5),
        ]);

        // 4. Past end date: excluded
        $expiredPopup = PromotionalPopup::create([
            'title' => ['es' => 'Expirado'],
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(),
        ]);

        $results = PromotionalPopup::query()->active()->get();

        $this->assertTrue($results->contains($alwaysActive));
        $this->assertTrue($results->contains($currentlyActive));
        $this->assertFalse($results->contains($futurePopup));
        $this->assertFalse($results->contains($expiredPopup));
    }

    public function test_scope_ordered_sorts_by_sort_order_asc_then_id_desc(): void
    {
        $item1 = PromotionalPopup::create([
            'title' => ['es' => 'Orden 10 - primero creado'],
            'sort_order' => 10,
        ]);

        $item2 = PromotionalPopup::create([
            'title' => ['es' => 'Orden 5'],
            'sort_order' => 5,
        ]);

        $item3 = PromotionalPopup::create([
            'title' => ['es' => 'Orden 10 - segundo creado (mayor ID)'],
            'sort_order' => 10,
        ]);

        $ordered = PromotionalPopup::query()->ordered()->get();

        $this->assertSame($item2->id, $ordered[0]->id);
        $this->assertSame($item3->id, $ordered[1]->id);
        $this->assertSame($item1->id, $ordered[2]->id);
    }

    public function test_get_localized_helpers_return_translated_string_or_fallback_to_es(): void
    {
        $bilingual = PromotionalPopup::create([
            'title' => [
                'es' => 'Título en español',
                'en' => 'Title in English',
            ],
            'subtitle' => [
                'es' => 'Subtítulo en español',
                'en' => 'Subtitle in English',
            ],
            'cta_text' => [
                'es' => 'Comprar ya',
                'en' => 'Shop now',
            ],
        ]);

        $spanishOnly = PromotionalPopup::create([
            'title' => [
                'es' => 'Solo título español',
            ],
            'subtitle' => [
                'es' => 'Solo subtítulo español',
            ],
            'cta_text' => [
                'es' => 'Solo botón español',
            ],
        ]);

        // When locale is es
        App::setLocale('es');
        $this->assertSame('Título en español', $bilingual->getLocalizedTitle());
        $this->assertSame('Subtítulo en español', $bilingual->getLocalizedSubtitle());
        $this->assertSame('Comprar ya', $bilingual->getLocalizedCtaText());

        $this->assertSame('Solo título español', $spanishOnly->getLocalizedTitle());
        $this->assertSame('Solo subtítulo español', $spanishOnly->getLocalizedSubtitle());
        $this->assertSame('Solo botón español', $spanishOnly->getLocalizedCtaText());

        // When locale is en
        App::setLocale('en');
        $this->assertSame('Title in English', $bilingual->getLocalizedTitle());
        $this->assertSame('Subtitle in English', $bilingual->getLocalizedSubtitle());
        $this->assertSame('Shop now', $bilingual->getLocalizedCtaText());

        // Fallback to es when en is missing
        $this->assertSame('Solo título español', $spanishOnly->getLocalizedTitle());
        $this->assertSame('Solo subtítulo español', $spanishOnly->getLocalizedSubtitle());
        $this->assertSame('Solo botón español', $spanishOnly->getLocalizedCtaText());

        // Explicit parameter override
        $this->assertSame('Título en español', $bilingual->getLocalizedTitle('es'));
        $this->assertSame('Title in English', $bilingual->getLocalizedTitle('en'));
    }

    public function test_it_relates_to_coupon_and_evaluates_has_valid_coupon_correctly(): void
    {
        $validCoupon = Coupon::factory()->create([
            'code' => 'SPRING20',
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addWeek(),
            'usage_limit' => 100,
            'used_count' => 10,
        ]);

        $inactiveCoupon = Coupon::factory()->create([
            'code' => 'INACTIVE',
            'is_active' => false,
        ]);

        $expiredCoupon = Coupon::factory()->create([
            'code' => 'EXPIRED',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $exhaustedCoupon = Coupon::factory()->create([
            'code' => 'EXHAUSTED',
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 5,
        ]);

        $popupWithValid = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up con cupón válido'],
            'coupon_id' => $validCoupon->id,
            'is_active' => true,
        ]);

        $popupWithInactive = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up con cupón inactivo'],
            'coupon_id' => $inactiveCoupon->id,
            'is_active' => true,
        ]);

        $popupWithExpired = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up con cupón expirado'],
            'coupon_id' => $expiredCoupon->id,
            'is_active' => true,
        ]);

        $popupWithExhausted = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up con cupón agotado'],
            'coupon_id' => $exhaustedCoupon->id,
            'is_active' => true,
        ]);

        $popupWithoutCoupon = PromotionalPopup::create([
            'title' => ['es' => 'Pop-up sin cupón'],
            'coupon_id' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($popupWithValid->hasValidCoupon());
        $this->assertSame('SPRING20', $popupWithValid->coupon->code);

        $this->assertFalse($popupWithInactive->hasValidCoupon());
        $this->assertFalse($popupWithExpired->hasValidCoupon());
        $this->assertFalse($popupWithExhausted->hasValidCoupon());
        $this->assertFalse($popupWithoutCoupon->hasValidCoupon());
    }
}
