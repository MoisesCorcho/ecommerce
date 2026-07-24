<?php

declare(strict_types=1);

namespace Tests\Feature\Storefront;

use Tests\TestCase;

class DesignTokensAndFontsTest extends TestCase
{
    public function test_app_css_contains_brand_color_tokens(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--color-silk-cream', $css);
        $this->assertStringContainsString('--color-soft-sand', $css);
        $this->assertStringContainsString('--color-intense-cocoa', $css);
        $this->assertStringContainsString('--color-soft-gold', $css);
        $this->assertStringContainsString('--color-error', $css);
        $this->assertStringContainsString('--color-success', $css);
    }

    public function test_app_css_contains_font_family_tokens(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--font-chillax', $css);
        $this->assertStringContainsString('--font-labelle-aurore', $css);
        $this->assertStringContainsString('Montserrat', $css);
    }

    public function test_app_css_contains_type_scale_and_spacing_tokens(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('--text-display-lg', $css);
        $this->assertStringContainsString('--text-headline-md', $css);
        $this->assertStringContainsString('--text-label-caps', $css);
        $this->assertStringContainsString('--text-accent-script', $css);
        $this->assertStringContainsString('--container-storefront', $css);
        $this->assertStringContainsString('--spacing-margin-desktop', $css);
        $this->assertStringContainsString('--spacing-margin-mobile', $css);
        $this->assertStringContainsString('--shadow-ambient', $css);
    }

    public function test_app_css_imports_fonts_css(): void
    {
        $css = (string) file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('fonts.css', $css);
    }

    public function test_fonts_css_contains_font_face_for_chillax(): void
    {
        $css = (string) file_get_contents(resource_path('css/fonts.css'));

        $this->assertStringContainsString('@font-face', $css);
        $this->assertStringContainsString('"Chillax"', $css);
        $this->assertStringContainsString('data:font/woff2;base64,', $css);
        $this->assertStringContainsString('font-display: swap', $css);
    }

    public function test_fonts_css_contains_font_face_for_montserrat(): void
    {
        $css = (string) file_get_contents(resource_path('css/fonts.css'));

        $this->assertStringContainsString('@import', $css);
        $this->assertStringContainsString('fonts.googleapis.com', $css);
        $this->assertStringContainsString('family=Montserrat', $css);
    }

    public function test_fonts_css_contains_font_face_for_la_belle_aurore(): void
    {
        $css = (string) file_get_contents(resource_path('css/fonts.css'));

        $this->assertStringContainsString('"La Belle Aurore"', $css);
        $this->assertStringContainsString('data:font/truetype;base64,', $css);
    }

    public function test_chillax_font_files_deployed_to_public(): void
    {
        $this->assertFileExists(public_path('fonts/chillax/Chillax-Regular.woff2'));
        $this->assertFileExists(public_path('fonts/chillax/Chillax-Bold.woff2'));
    }

    public function test_montserrat_font_file_deployed_to_public(): void
    {
        $this->assertFileExists(public_path('fonts/montserrat/Montserrat-VariableFont_wght.ttf'));
    }

    public function test_la_belle_aurore_font_file_deployed_to_public(): void
    {
        $this->assertFileExists(public_path('fonts/labelle-aurore/LaBelleAurore-Regular.ttf'));
    }
}
