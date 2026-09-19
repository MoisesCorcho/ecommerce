<?php

declare(strict_types=1);

namespace App\DTOs\Products;

readonly class UpsertProductDTO
{
    /**
     * @param  list<UpsertProductVariantDTO>  $variants
     * @param  list<UpsertProductImageDTO>  $images
     */
    public function __construct(
        public string $name,
        public ?string $slug,
        public bool $isActive,
        public bool $isPreorder,
        public array $variants,
        public array $images = [],
        public ?int $categoryId = null,
        public ?string $description = null,
        public ?string $material = null,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     slug?: string|null,
     *     is_active?: bool|int|string|null,
     *     is_preorder?: bool|int|string|null,
     *     category_id?: int|string|null,
     *     description?: string|null,
     *     material?: string|null,
     *     variants?: list<array<string, mixed>>,
     *     images?: list<array<string, mixed>>
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        $variants = [];
        foreach ($data['variants'] ?? [] as $variantData) {
            $variants[] = UpsertProductVariantDTO::fromArray($variantData);
        }

        $images = [];
        foreach ($data['images'] ?? [] as $imageData) {
            if (empty($imageData['path'])) {
                continue;
            }
            $images[] = UpsertProductImageDTO::fromArray($imageData);
        }

        $slug = $data['slug'] ?? null;
        $slug = is_string($slug) && trim($slug) !== '' ? trim($slug) : null;

        return new self(
            name: (string) $data['name'],
            slug: $slug,
            isActive: filter_var($data['is_active'] ?? false, FILTER_VALIDATE_BOOLEAN),
            isPreorder: filter_var($data['is_preorder'] ?? false, FILTER_VALIDATE_BOOLEAN),
            variants: $variants,
            images: $images,
            categoryId: isset($data['category_id']) && $data['category_id'] !== '' && $data['category_id'] !== null
                ? (int) $data['category_id']
                : null,
            description: isset($data['description']) && $data['description'] !== ''
                ? (string) $data['description']
                : null,
            material: isset($data['material']) && $data['material'] !== ''
                ? (string) $data['material']
                : null,
        );
    }
}
