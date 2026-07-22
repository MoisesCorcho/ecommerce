<?php

declare(strict_types=1);

namespace App\DTOs\Payments;

use App\Models\Payment;

readonly class StartOrderPaymentResultDTO
{
    public function __construct(
        public Payment $payment,
        public string $redirectUrl,
    ) {}
}
