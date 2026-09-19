<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Coupon;
use App\Models\PromotionalPopup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionalPopupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_promotional_popup_is_rendered_when_active_popup_exists(): void
    {
        PromotionalPopup::factory()->create([
            'title' => [
                'es' => '¡Bienvenido a Leen!',
                'en' => 'Welcome to Leen!',
            ],
            'subtitle' => [
                'es' => 'Obtén 10% en tu primer bolso.',
                'en' => 'Get 10% off your first handbag.',
            ],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('¡Bienvenido a Leen!');
        $response->assertSee('Obtén 10% en tu primer bolso.');
        $response->assertSee('Una oferta más dulce que la miel');
        $response->assertSee('dusk="promotional-popup"', false);
    }

    public function test_promotional_popup_is_not_rendered_when_no_active_popup_exists(): void
    {
        PromotionalPopup::factory()->inactive()->create([
            'title' => ['es' => 'Pop-up inactivo'],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('dusk="promotional-popup"', false);
        $response->assertDontSee('Pop-up inactivo');
    }

    public function test_promotional_popup_is_not_rendered_when_out_of_date_window(): void
    {
        // Future start date
        PromotionalPopup::factory()->create([
            'title' => ['es' => 'A futuro'],
            'is_active' => true,
            'starts_at' => now()->addDays(2),
        ]);

        // Past end date
        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Expirado'],
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('dusk="promotional-popup"', false);
    }

    public function test_promotional_popup_renders_coupon_and_copy_button_when_coupon_is_valid(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'LEEN10',
            'type' => CouponTypeEnum::Percentage,
            'value' => 10,
            'is_active' => true,
        ]);

        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con cupón'],
            'coupon_id' => $coupon->id,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('LEEN10');
        $response->assertSee('dusk="copy-coupon-btn"', false);
    }

    public function test_promotional_popup_hides_coupon_block_when_coupon_is_inactive_or_expired(): void
    {
        $inactiveCoupon = Coupon::factory()->create([
            'code' => 'EXPIREDCODE',
            'is_active' => false,
        ]);

        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con cupón inválido'],
            'coupon_id' => $inactiveCoupon->id,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('Pop-up con cupón inválido');
        $response->assertDontSee('EXPIREDCODE');
        $response->assertDontSee('dusk="copy-coupon-btn"', false);
    }

    public function test_promotional_popup_displays_english_when_locale_is_en(): void
    {
        PromotionalPopup::factory()->create([
            'title' => [
                'es' => 'Título en Español',
                'en' => 'Title in English',
            ],
            'subtitle' => [
                'es' => 'Subtítulo en Español',
                'en' => 'Subtitle in English',
            ],
            'cta_text' => [
                'es' => 'Comprar',
                'en' => 'Shop Now',
            ],
            'cta_url' => '/shop',
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('Title in English');
        $response->assertSee('Subtitle in English');
        $response->assertSee('Shop Now');
        $response->assertSee('An offer sweeter than honey');
        $response->assertDontSee('Título en Español');
        $response->assertDontSee('Una oferta más dulce que la miel');
    }

    public function test_promotional_popup_includes_alpine_1_day_dismiss_logic(): void
    {
        $popup = PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con descarte'],
            'delay_seconds' => 7,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('x-data="{ show: false, copied: false, id: '.$popup->id.', delay: 7 }"', false);
        $response->assertSee("const dismissedKey = 'leen_popup_dismissed_' + id;", false);
        $response->assertSee('const oneDayMs = 24 * 60 * 60 * 1000;', false);
        $response->assertSee("localStorage.setItem('leen_popup_dismissed_' + id, Date.now().toString())", false);
    }

    public function test_promotional_popup_cta_click_persists_dismissal_in_local_storage(): void
    {
        $popup = PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con CTA'],
            'cta_text' => ['es' => 'Aprovechar Oferta'],
            'cta_url' => '/shop',
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('href="/shop"', false);
        $response->assertSee('@click="localStorage.setItem(\'leen_popup_dismissed_\' + id, Date.now().toString())"', false);
    }

    public function test_promotional_popup_includes_responsive_classes(): void
    {
        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up adaptativo'],
            'subtitle' => ['es' => 'Subtítulo'],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('my-auto', false);
        $response->assertSee('no-scrollbar', false);
        $response->assertSee('max-h-[calc(100vh-2rem)]', false);
    }

    public function test_promotional_popup_with_image_uses_horizontal_split_layout_on_laptop(): void
    {
        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con imagen'],
            'image_path' => 'popups/test-banner.jpg',
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('laptop-horizontal:max-w-2xl', false);
        $response->assertSee('laptop-horizontal:flex-row', false);
        $response->assertSee('laptop-horizontal:w-5/12', false);
    }

    public function test_promotional_popup_without_image_preserves_compact_single_column_layout(): void
    {
        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up sin imagen'],
            'image_path' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('max-w-lg', false);
        $response->assertDontSee('laptop-horizontal:max-w-2xl', false);
        $response->assertDontSee('laptop-horizontal:w-5/12', false);
    }

    public function test_copy_button_checkmark_icon_has_accessible_hover_contrast(): void
    {
        $coupon = Coupon::factory()->create([
            'code' => 'ACCESSIBLE10',
            'is_active' => true,
        ]);

        PromotionalPopup::factory()->create([
            'title' => ['es' => 'Pop-up con cupón'],
            'coupon_id' => $coupon->id,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('text-soft-gold group-hover:text-silk-cream', false);
        $response->assertSee('focus-visible:ring-intense-cocoa', false);
        $response->assertSee('aria-live="polite"', false);
    }
}
