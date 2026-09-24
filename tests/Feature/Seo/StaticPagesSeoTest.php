<?php

declare(strict_types=1);

namespace Tests\Feature\Seo;

use Tests\TestCase;

class StaticPagesSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        app()->setLocale('es');
    }

    public function test_about_page_renders_seo_title_description_and_canonical(): void
    {
        $response = $this->get(route('about'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Quiénes Somos | LEEN — Lujo Consciente y Tradición</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('about').'">', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Conoce la historia detrás de LEEN', $content);
    }

    public function test_contact_page_renders_seo_title_description_and_canonical(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Contacto | LEEN — Atención Personalizada</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('contact').'">', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Ponte en contacto con nuestro equipo', $content);
    }

    public function test_faq_page_renders_seo_title_description_and_canonical(): void
    {
        $response = $this->get(route('faq'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Preguntas Frecuentes | LEEN</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('faq').'">', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Respuestas a dudas habituales', $content);
    }

    public function test_blog_index_renders_seo_title_description_and_canonical(): void
    {
        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('<title>Blog | LEEN — Historias, Diseño y Artesanía</title>', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('blog.index').'">', $content);
        $this->assertStringContainsString('<meta name="description"', $content);
        $this->assertStringContainsString('Historias de taller, guías de estilo', $content);
    }
}
