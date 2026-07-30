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
            throw new InvalidArgumentException(__('products.validation.price_non_negative'));
        }

        if ($compareAtPrice !== null && $compareAtPrice < 0) {
            throw new InvalidArgumentException(__('products.validation.compare_at_price_non_negative'));
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
            throw new InvalidArgumentException(__('products.validation.price_non_negative'));
        }

        $compareAt = $data['compare_at_price'] ?? null;
        if ($compareAt !== null && $compareAt !== '') {
            if (! is_numeric($compareAt) || (string) (int) $compareAt !== (string) $compareAt) {
                throw new InvalidArgumentException(__('products.validation.compare_at_price_non_negative'));
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
