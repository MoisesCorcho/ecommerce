<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Pages;

use App\Actions\Coupons\UpdateCouponAction;
use App\DTOs\Coupons\UpsertCouponDTO;
use App\Exceptions\Coupons\CouponImmutableFieldsException;
use App\Filament\Resources\Coupons\CouponResource;
use App\Models\Coupon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditCoupon extends EditRecord
{
    protected static string $resource = CouponResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('coupons.pages.edit_title');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('coupons.notifications.updated');
    }

    protected function getHeaderActions(): array
    {
        // No hard-delete (D30).
        return [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Coupon $record */
        try {
            return app(UpdateCouponAction::class)($record, UpsertCouponDTO::fromArray($data));
        } catch (CouponImmutableFieldsException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();

            $this->halt();

            return $record;
        } catch (ValidationException $exception) {
            throw ValidationException::withMessages(
                collect($exception->errors())
                    ->mapWithKeys(static fn (array $messages, string $key): array => ["data.{$key}" => $messages])
                    ->all()
            );
        }
    }
}
