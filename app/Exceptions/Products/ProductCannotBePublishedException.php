<?php

declare(strict_types=1);

namespace App\Exceptions\Products;

use RuntimeException;

/**
 * Thrown when a product is saved as active without a sellable variant+price graph.
 */
class ProductCannotBePublishedException extends RuntimeException
{
    public static function missingActiveVariantWithPrice(): self
    {
        return new self(
            __('products.exceptions.cannot_publish_missing_variant_price')
        );
    }
}
