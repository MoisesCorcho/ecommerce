<?php

declare(strict_types=1);

namespace App\DTOs\Products;

use App\Enums\Products\SizeEnum;
use App\Models\Color;
use App\Support\ColorMap;
use Illuminate\Support\Str;

readonly class UpsertProductVariantDTO
{
    /**
     * @param  list<UpsertProductVariantPriceDTO>  $prices
     */
    public function __construct(
        public string $sku,
        public bool $isActive,
        public int $stock,
        public array $prices,
        public ?int $colorId = null,
        public SizeEnum|string|null $size = null,
        public ?string $dimensions = null,
        public ?int $id = null,
    ) {}

    /**
     * @param  array{
     *     sku: string,
     *     is_active?: bool|int|string|null,
     *     stock?: int|string|null,
     *     color?: string|null,
     *     color_id?: int|string|null,
     *     size?: SizeEnum|string|null,
     *     dimensions?: string|null,
     *     id?: int|null,
     *     prices?: list<array<string, mixed>>
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        $prices = [];
        foreach ($data['prices'] ?? [] as $priceData) {
            $prices[] = UpsertProductVariantPriceDTO::fromArray($priceData);
        }

        $size = match (true) {
            isset($data['size']) && $data['size'] instanceof SizeEnum => $data['size']->value,
            isset($data['size']) && is_string($data['size']) && $data['size'] !== '' => SizeEnum::tryFrom($data['size'])?->value
                ?? SizeEnum::tryFrom(strtolower($data['size']))?->value
                ?? $data['size'],
            default => null,
        };

        $colorId = null;
        if (isset($data['color_id']) && $data['color_id'] !== '' && $data['color_id'] !== null) {
            $colorId = (int) $data['color_id'];
        } elseif (isset($data['color']) && is_string($data['color']) && $data['color'] !== '') {
            $colorName = trim($data['color']);
            $color = Color::query()
                ->where('name', $colorName)
                ->orWhere('slug', Str::slug($colorName))
                ->first()
                ?? Color::create([
                    'name' => $colorName,
                    'slug' => Str::slug($colorName),
                    'hex_code' => ColorMap::for($colorName),
                    'is_active' => true,
                ]);
            $colorId = $color->id;
        }

        return new self(
            sku: (string) $data['sku'],
            isActive: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            stock: (int) ($data['stock'] ?? 0),
            prices: $prices,
            colorId: $colorId,
            size: $size,
            dimensions: isset($data['dimensions']) && $data['dimensions'] !== '' ? (string) $data['dimensions'] : null,
            id: isset($data['id']) ? (int) $data['id'] : null,
        );
    }
}
