<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Color;
use App\Support\ColorMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColorMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_rojo_oscuro_casing_resolves_after_lowercasing(): void
    {
        $this->assertArrayHasKey(strtolower('Rojo oscuro'), ColorMap::HEX);
        $this->assertSame('#4A1A1A', ColorMap::HEX[strtolower('Rojo oscuro')]);
    }

    public function test_new_basic_color_keys_are_present(): void
    {
        $expected = [
            'amarillo' => '#C9A227',
            'yellow' => '#C9A227',
            'naranja' => '#C1672B',
            'orange' => '#C1672B',
            'beige' => '#E3D5B8',
            'púrpura' => '#5B3A5E',
            'purpura' => '#5B3A5E',
            'purple' => '#5B3A5E',
            'morado' => '#5B3A5E',
            'blanco' => '#F5F1E8',
            'white' => '#F5F1E8',
            'dark red' => '#4A1A1A',
        ];

        foreach ($expected as $key => $hex) {
            $this->assertSame($hex, ColorMap::HEX[$key] ?? null, "Missing or mismatched hex for key [{$key}]");
        }
    }

    public function test_color_map_for_resolves_color_and_falls_back_gracefully(): void
    {
        $this->assertSame('#201b14', ColorMap::for('Negro'));
        $this->assertSame('#8B5A2B', ColorMap::for('cognac'));
        $this->assertSame('#8B8B8B', ColorMap::for('non-existent-color-12345'));
        $this->assertSame('#8B8B8B', ColorMap::for(null));
        $this->assertSame('#CUSTOM', ColorMap::for(null, '#CUSTOM'));
    }

    public function test_color_map_for_resolves_dynamic_color_from_database(): void
    {
        Color::create([
            'name' => 'Verde Menta',
            'hex_code' => '#98FF98',
            'is_active' => true,
        ]);

        $this->assertSame('#98FF98', ColorMap::for('Verde Menta'));
        $this->assertSame('#98FF98', ColorMap::for('verde-menta'));
    }
}
