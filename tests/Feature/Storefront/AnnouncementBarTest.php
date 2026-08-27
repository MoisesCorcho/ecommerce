<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementBarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_announcement_bar_is_rendered_when_active_announcement_exists(): void
    {
        Announcement::factory()->create([
            'text' => [
                'es' => '¡Envío gratis a partir de $150.000!',
                'en' => 'Free shipping over $150,000!',
            ],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('¡Envío gratis a partir de $150.000!');
        $response->assertSee('dusk="announcement-bar"', false);
    }

    public function test_announcement_bar_is_not_rendered_when_no_active_announcement_exists(): void
    {
        Announcement::factory()->inactive()->create([
            'text' => ['es' => 'Aviso inactivo'],
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('dusk="announcement-bar"', false);
        $response->assertDontSee('Aviso inactivo');
    }

    public function test_announcement_bar_is_not_rendered_when_announcement_is_out_of_date_window(): void
    {
        // Future start date
        Announcement::factory()->create([
            'text' => ['es' => 'Futuro'],
            'is_active' => true,
            'starts_at' => now()->addDays(2),
        ]);

        // Past end date
        Announcement::factory()->create([
            'text' => ['es' => 'Expirado'],
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('dusk="announcement-bar"', false);
    }

    public function test_announcement_bar_displays_spanish_text_when_locale_is_es(): void
    {
        Announcement::factory()->create([
            'text' => [
                'es' => 'Texto en Español',
                'en' => 'Text in English',
            ],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('Texto en Español');
        $response->assertDontSee('Text in English');
    }

    public function test_announcement_bar_displays_english_text_when_locale_is_en(): void
    {
        Announcement::factory()->create([
            'text' => [
                'es' => 'Texto en Español',
                'en' => 'Text in English',
            ],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('Text in English');
        $response->assertDontSee('Texto en Español');
    }

    public function test_announcement_bar_falls_back_to_spanish_when_english_is_empty(): void
    {
        Announcement::factory()->create([
            'text' => [
                'es' => 'Solo disponible en español',
            ],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('Solo disponible en español');
    }

    public function test_announcement_bar_renders_external_link_with_target_blank_and_rel_noopener(): void
    {
        Announcement::factory()->create([
            'text' => ['es' => 'Enlace externo'],
            'url' => 'https://instagram.com/leen',
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('href="https://instagram.com/leen"', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_announcement_bar_renders_internal_link_without_target_blank(): void
    {
        Announcement::factory()->create([
            'text' => ['es' => 'Ver tienda'],
            'url' => '/shop',
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('href="/shop"', false);
        $response->assertSee('<span>Ver tienda</span>', false);
    }

    public function test_announcement_bar_renders_as_plain_text_when_url_is_null(): void
    {
        Announcement::factory()->create([
            'text' => ['es' => 'Solo texto plano sin enlace'],
            'url' => null,
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('<span>Solo texto plano sin enlace</span>', false);
    }

    public function test_announcement_bar_includes_alpine_dismiss_attributes(): void
    {
        $announcement = Announcement::factory()->create([
            'text' => ['es' => 'Aviso con cierre'],
            'is_active' => true,
        ]);

        $response = $this->withSession(['locale' => 'es'])->get('/');

        $response->assertOk();
        $response->assertSee('x-data="{ dismissed: false, id: '.$announcement->id.' }"', false);
        $response->assertSee("localStorage.getItem('leen_announcement_dismissed_' + id)", false);
        $response->assertSee('x-cloak', false);
    }
}
