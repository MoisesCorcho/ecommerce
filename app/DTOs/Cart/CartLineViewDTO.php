<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

readonly class CartLineViewDTO
{
    public function __construct(
        public int $cartItemId,
        public int $productVariantId,
        public int $quantity,
        public int $unitPrice,
        public int $lineSubtotal,
        public ?string $sku = null,
        public ?string $productName = null,
        public ?string $imagePath = null,
        public ?string $productSlug = null,
        public ?string $color = null,
        public ?string $size = null,
        public ?string $material = null,
        public int $stock = 0,
        public bool $isAvailable = true,
    ) {}
}
