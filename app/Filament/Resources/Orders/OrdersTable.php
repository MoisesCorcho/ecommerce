<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('orders.fields.order_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('orders.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('orders.fields.status'))
                    ->badge()
                    ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('orders.fields.currency'))
                    ->sortable(),
                TextColumn::make('total')
                    ->label(__('orders.fields.total'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('orders.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('orders.fields.status'))
                    ->options(collect(OrderStatusEnum::cases())->mapWithKeys(
                        fn (OrderStatusEnum $status): array => [$status->value => $status->label()],
                    )->all()),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record): string => ViewOrder::getUrl(['record' => $record])),
            ]);
    }
}
