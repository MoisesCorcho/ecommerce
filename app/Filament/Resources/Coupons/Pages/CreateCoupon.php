<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Pages;

use App\Actions\Coupons\CreateCouponAction;
use App\DTOs\Coupons\UpsertCouponDTO;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateCoupon extends CreateRecord
{
    protected static string $resource = CouponResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('coupons.pages.create_title');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('coupons.notifications.created');
    }

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(CreateCouponAction::class)(UpsertCouponDTO::fromArray($data));
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(
                collect($exception->errors())
                    ->mapWithKeys(static fn (array $messages, string $key): array => ["data.{$key}" => $messages])
                    ->all()
            );
        }
    }
}
