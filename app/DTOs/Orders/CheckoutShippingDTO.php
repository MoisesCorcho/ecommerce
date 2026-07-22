<?php

declare(strict_types=1);

namespace App\DTOs\Orders;

readonly class CheckoutShippingDTO
{
    public function __construct(
        public string $fullName,
        public string $phone,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $state,
        public string $country,
        public ?string $postalCode = null,
        public ?int $addressId = null,
    ) {}
}
