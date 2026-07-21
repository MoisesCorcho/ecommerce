<?php

declare(strict_types=1);

namespace App\DTOs\Products;

use App\Enums\Commerce\CurrencyEnum;
use InvalidArgumentException;

readonly class UpsertProductVariantPriceDTO
{
    public function __construct(
        public CurrencyEnum $currency,
        public int $price,
        public ?int $compareAtPrice = null,
        public ?int $id = null,
    ) {
        if ($price < 0) {
            throw new InvalidArgumentException('El precio debe ser un entero no negativo.');
        }

        if ($compareAtPrice !== null && $compareAtPrice < 0) {
            throw new InvalidArgumentException('El precio de comparación debe ser un entero no negativo.');
        }
    }

    /**
     * @param  array{currency: string|CurrencyEnum, price: int|string, compare_at_price?: int|string|null, id?: int|null}  $data
     */
    public static function fromArray(array $data): self
    {
        $currency = $data['currency'] instanceof CurrencyEnum
            ? $data['currency']
            : CurrencyEnum::from((string) $data['currency']);

        if (! is_numeric($data['price']) || (string) (int) $data['price'] !== (string) $data['price']) {
            throw new InvalidArgumentException('El precio debe ser un entero no negativo.');
        }

        $compareAt = $data['compare_at_price'] ?? null;
        if ($compareAt !== null && $compareAt !== '') {
            if (! is_numeric($compareAt) || (string) (int) $compareAt !== (string) $compareAt) {
                throw new InvalidArgumentException('El precio de comparación debe ser un entero no negativo.');
            }
            $compareAt = (int) $compareAt;
        } else {
            $compareAt = null;
        }

        return new self(
            currency: $currency,
            price: (int) $data['price'],
            compareAtPrice: $compareAt,
            id: isset($data['id']) ? (int) $data['id'] : null,
        );
    }
}
