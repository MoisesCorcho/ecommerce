<?php

declare(strict_types=1);

namespace App\DTOs\Orders;

readonly class CreateOrderFromCartDTO
{
    public function __construct(
        public int $cartId,
        public CheckoutContactDTO $contact,
        public CheckoutShippingDTO $shipping,
        public ?int $userId = null,
        public ?string $sessionId = null,
        public ?string $customerNotes = null,
        public ?string $couponCode = null,
    ) {}
}
