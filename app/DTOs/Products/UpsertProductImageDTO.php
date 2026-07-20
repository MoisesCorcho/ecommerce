<?php

declare(strict_types=1);

namespace App\DTOs\Products;

readonly class UpsertProductImageDTO
{
    public function __construct(
        public string $path,
        public int $sortOrder = 0,
        public bool $isPrimary = false,
        public ?int $productVariantId = null,
        public ?int $id = null,
    ) {}

    /**
     * @param  array{
     *     path: string,
     *     sort_order?: int|string|null,
     *     is_primary?: bool|int|string|null,
     *     product_variant_id?: int|null,
     *     id?: int|null
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            path: (string) $data['path'],
            sortOrder: (int) ($data['sort_order'] ?? 0),
            isPrimary: filter_var($data['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN),
            productVariantId: isset($data['product_variant_id']) ? (int) $data['product_variant_id'] : null,
            id: isset($data['id']) ? (int) $data['id'] : null,
        );
    }
}
