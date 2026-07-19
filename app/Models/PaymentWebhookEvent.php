<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentProviderEnum;
use Database\Factories\PaymentWebhookEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'event_id',
    'event_type',
    'payload',
    'processed_at',
])]
class PaymentWebhookEvent extends Model
{
    /** @use HasFactory<PaymentWebhookEventFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => PaymentProviderEnum::class,
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
