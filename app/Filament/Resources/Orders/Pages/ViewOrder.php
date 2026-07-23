<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Pages;

use App\Actions\Orders\CancelOrderAction;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Orders\OrderCannotBeCancelledException;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function resolveRecord(int|string $key): Order
    {
        /** @var Order $record */
        $record = parent::resolveRecord($key);
        $record->loadMissing(['items', 'payments']);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label(__('orders.actions.cancel'))
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->getRecord()->status === OrderStatusEnum::Pending)
                ->action(function (CancelOrderAction $cancelOrder): void {
                    /** @var Order $order */
                    $order = $this->getRecord();

                    try {
                        $cancelOrder((int) $order->id);
                        Notification::make()
                            ->title(__('orders.notifications.cancelled'))
                            ->success()
                            ->send();
                        $this->refreshFormData(['status']);
                        $this->record->refresh();
                    } catch (OrderCannotBeCancelledException $e) {
                        Notification::make()
                            ->title($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
