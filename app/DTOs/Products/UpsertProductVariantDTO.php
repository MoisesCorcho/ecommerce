<?php

declare(strict_types=1);

namespace App\DTOs\Products;

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
        public ?string $color = null,
        public ?string $size = null,
        public ?int $id = null,
    ) {}

    /**
     * @param  array{
     *     sku: string,
     *     is_active?: bool|int|string|null,
     *     stock?: int|string|null,
     *     color?: string|null,
     *     size?: string|null,
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

        return new self(
            sku: (string) $data['sku'],
            isActive: filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            stock: (int) ($data['stock'] ?? 0),
            prices: $prices,
            color: isset($data['color']) && $data['color'] !== '' ? (string) $data['color'] : null,
            size: isset($data['size']) && $data['size'] !== '' ? (string) $data['size'] : null,
            id: isset($data['id']) ? (int) $data['id'] : null,
        );
    }
}
