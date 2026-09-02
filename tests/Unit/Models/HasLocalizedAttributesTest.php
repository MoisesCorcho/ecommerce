<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\PromotionalPopup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasLocalizedAttributesTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_localized_attribute_in_active_locale(): void
    {
        $post = Post::factory()->create([
            'title' => [
                'es' => 'Título en Español',
                'en' => 'Title in English',
            ],
        ]);

        app()->setLocale('en');
        $this->assertSame('Title in English', $post->getLocalizedTitle());

        app()->setLocale('es');
        $this->assertSame('Título en Español', $post->getLocalizedTitle());
    }

    public function test_falls_back_to_spanish_when_requested_locale_translation_is_missing(): void
    {
        $post = Post::factory()->create([
            'title' => [
                'es' => 'Título Solo en Español',
            ],
        ]);

        app()->setLocale('en');
        $this->assertSame('Título Solo en Español', $post->getLocalizedTitle());
    }

    public function test_returns_empty_string_when_translation_does_not_exist_in_any_locale(): void
    {
        $post = Post::factory()->create([
            'title' => [],
        ]);

        app()->setLocale('en');
        $this->assertSame('', $post->getLocalizedTitle());
    }

    public function test_resolves_nullable_attribute_returning_null_when_empty(): void
    {
        $popup = PromotionalPopup::factory()->create([
            'subtitle' => [],
            'cta_text' => [
                'es' => 'Comprar Ahora',
            ],
        ]);

        app()->setLocale('es');
        $this->assertNull($popup->getLocalizedSubtitle());
        $this->assertSame('Comprar Ahora', $popup->getLocalizedCtaText());
    }
}
