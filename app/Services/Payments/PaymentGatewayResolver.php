<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Enums\Payments\PaymentProviderEnum;
use App\Gateways\Payments\BoldPaymentGateway;
use App\Gateways\Payments\StripePaymentGateway;
use InvalidArgumentException;

class PaymentGatewayResolver
{
    public function for(PaymentProviderEnum $provider): PaymentGatewayInterface
    {
        $binding = 'payment.gateway.'.$provider->value;

        if (app()->bound($binding)) {
            /** @var PaymentGatewayInterface $gateway */
            $gateway = app($binding);

            return $gateway;
        }

        return match ($provider) {
            PaymentProviderEnum::Stripe => app(StripePaymentGateway::class),
            PaymentProviderEnum::Bold => app(BoldPaymentGateway::class),
            default => throw new InvalidArgumentException("Unsupported payment provider [{$provider->value}]."),
        };
    }
}
