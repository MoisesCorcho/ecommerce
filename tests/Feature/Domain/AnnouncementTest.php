<?php

declare(strict_types=1);

namespace Tests\Feature\Domain;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_announcement_with_translatable_text(): void
    {
        $announcement = Announcement::create([
            'text' => [
                'es' => '¡Envío gratis en compras mayores a $150.000!',
                'en' => 'Free shipping on orders over $150,000!',
            ],
            'url' => 'https://leen.com.co/shop',
            'is_active' => true,
            'sort_order' => 1,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(5),
        ]);

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'url' => 'https://leen.com.co/shop',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame('¡Envío gratis en compras mayores a $150.000!', $announcement->getTranslation('text', 'es'));
        $this->assertSame('Free shipping on orders over $150,000!', $announcement->getTranslation('text', 'en'));
    }

    public function test_it_casts_attributes_correctly(): void
    {
        $announcement = Announcement::create([
            'text' => ['es' => 'Texto prueba'],
            'is_active' => 1,
            'sort_order' => '10',
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-31 23:59:59',
        ]);

        $this->assertIsBool($announcement->is_active);
        $this->assertTrue($announcement->is_active);
        $this->assertIsInt($announcement->sort_order);
        $this->assertSame(10, $announcement->sort_order);
        $this->assertInstanceOf(Carbon::class, $announcement->starts_at);
        $this->assertInstanceOf(Carbon::class, $announcement->ends_at);
    }

    public function test_scope_active_filters_by_is_active_flag(): void
    {
        $active = Announcement::create([
            'text' => ['es' => 'Anuncio activo'],
            'is_active' => true,
        ]);

        $inactive = Announcement::create([
            'text' => ['es' => 'Anuncio inactivo'],
            'is_active' => false,
        ]);

        $results = Announcement::query()->active()->get();

        $this->assertTrue($results->contains($active));
        $this->assertFalse($results->contains($inactive));
    }

    public function test_scope_active_filters_by_starts_at_and_ends_at(): void
    {
        // 1. Without dates: active
        $alwaysActive = Announcement::create([
            'text' => ['es' => 'Siempre activo'],
            'is_active' => true,
            'starts_at' => null,
            'ends_at' => null,
        ]);

        // 2. Currently within date window: active
        $currentlyActive = Announcement::create([
            'text' => ['es' => 'Vigente ahora'],
            'is_active' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
        ]);

        // 3. Future start date: excluded
        $futureAnnouncement = Announcement::create([
            'text' => ['es' => 'A futuro'],
            'is_active' => true,
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(5),
        ]);

        // 4. Past end date: excluded
        $expiredAnnouncement = Announcement::create([
            'text' => ['es' => 'Expirado'],
            'is_active' => true,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subDay(),
        ]);

        $results = Announcement::query()->active()->get();

        $this->assertTrue($results->contains($alwaysActive));
        $this->assertTrue($results->contains($currentlyActive));
        $this->assertFalse($results->contains($futureAnnouncement));
        $this->assertFalse($results->contains($expiredAnnouncement));
    }

    public function test_scope_ordered_sorts_by_sort_order_asc_then_id_desc(): void
    {
        $item1 = Announcement::create([
            'text' => ['es' => 'Orden 10 - primero creado'],
            'sort_order' => 10,
        ]);

        $item2 = Announcement::create([
            'text' => ['es' => 'Orden 5'],
            'sort_order' => 5,
        ]);

        $item3 = Announcement::create([
            'text' => ['es' => 'Orden 10 - segundo creado (mayor ID)'],
            'sort_order' => 10,
        ]);

        $ordered = Announcement::query()->ordered()->get();

        $this->assertSame($item2->id, $ordered[0]->id);
        $this->assertSame($item3->id, $ordered[1]->id);
        $this->assertSame($item1->id, $ordered[2]->id);
    }

    public function test_get_localized_text_returns_translated_string_or_fallback_to_es(): void
    {
        $bilingual = Announcement::create([
            'text' => [
                'es' => 'Hola en español',
                'en' => 'Hello in English',
            ],
        ]);

        $spanishOnly = Announcement::create([
            'text' => [
                'es' => 'Solo español',
            ],
        ]);

        // When locale is es
        App::setLocale('es');
        $this->assertSame('Hola en español', $bilingual->getLocalizedText());
        $this->assertSame('Solo español', $spanishOnly->getLocalizedText());

        // When locale is en
        App::setLocale('en');
        $this->assertSame('Hello in English', $bilingual->getLocalizedText());
        // Fallback to es when en is missing
        $this->assertSame('Solo español', $spanishOnly->getLocalizedText());

        // Parameter overrides app locale
        $this->assertSame('Hola en español', $bilingual->getLocalizedText('es'));
        $this->assertSame('Hello in English', $bilingual->getLocalizedText('en'));
    }
}
