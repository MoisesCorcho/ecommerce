<?php

declare(strict_types=1);

namespace App\DTOs\Orders;

readonly class CheckoutPreviewLineDTO
{
    public function __construct(
        public int $productVariantId,
        public string $productName,
        public ?string $variantLabel,
        public ?string $sku,
        public int $unitPrice,
        public int $quantity,
        public int $lineSubtotal,
    ) {}
}
