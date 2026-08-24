<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Color;
use Illuminate\Support\Str;

/**
 * Static color-name → hex map for product variant swatches.
 *
 * Shared between filter panel and product card color dots.
 */
final class ColorMap
{
    /** @var array<string, string> */
    public const HEX = [
        'negro' => '#201b14',
        'black' => '#201b14',
        'cognac' => '#8B5A2B',
        'marrón' => '#6B4226',
        'marron' => '#6B4226',
        'brown' => '#6B4226',
        'chocolate' => '#372621',
        'cocoa' => '#372621',
        'tan' => '#D2B48C',
        'camel' => '#C19A6B',
        'arena' => '#D2B48C',
        'sand' => '#D2B48C',
        'crema' => '#FAF3E0',
        'cream' => '#FAF3E0',
        'dorado' => '#D2AE36',
        'gold' => '#D2AE36',
        'dune' => '#C2A67D',
        'oliva' => '#6B6B3C',
        'olive' => '#6B6B3C',
        'verde' => '#5A6B3C',
        'green' => '#5A6B3C',
        'azul' => '#2C3E50',
        'blue' => '#2C3E50',
        'navy' => '#2C3E50',
        'burdeos' => '#6B2D3E',
        'burgundy' => '#6B2D3E',
        'wine' => '#6B2D3E',
        'vino' => '#6B2D3E',
        'rosa' => '#D4A0A0',
        'pink' => '#D4A0A0',
        'blush' => '#D4A0A0',
        'rojo' => '#8B3A3A',
        'red' => '#8B3A3A',
        'rojo oscuro' => '#4A1A1A',
        'dark red' => '#4A1A1A',
        'gris' => '#8B8B8B',
        'gray' => '#8B8B8B',
        'grey' => '#8B8B8B',
        'slate' => '#6B7B8D',
        'piedra' => '#9B9B8B',
        'stone' => '#9B9B8B',
        'nude' => '#D4B5A0',
        'camelot' => '#A67B5B',
        'whisky' => '#C5832A',
        'whiskey' => '#C5832A',
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
    ];

    public static function for(?string $colorName, ?string $fallback = '#8B8B8B'): string
    {
        if ($colorName === null || $colorName === '') {
            return $fallback ?? '#8B8B8B';
        }

        $trimmed = trim($colorName);
        $lower = strtolower($trimmed);

        if (isset(self::HEX[$lower])) {
            return self::HEX[$lower];
        }

        $colorModel = Color::query()
            ->where('name', $trimmed)
            ->orWhere('slug', Str::slug($trimmed))
            ->first();

        if ($colorModel && filled($colorModel->hex_code)) {
            return $colorModel->hex_code;
        }

        return $fallback ?? '#8B8B8B';
    }
}
