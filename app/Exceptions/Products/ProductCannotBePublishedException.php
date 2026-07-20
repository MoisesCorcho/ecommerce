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
            'No se puede publicar el producto: se requiere al menos una variante activa con al menos un precio en una moneda soportada.'
        );
    }
}
