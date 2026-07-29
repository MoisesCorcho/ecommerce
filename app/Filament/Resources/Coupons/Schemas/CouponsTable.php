<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Coupon;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->label(__('coupons.fields.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label(__('coupons.fields.type'))
                    ->badge()
                    ->formatStateUsing(fn (CouponTypeEnum $state): string => $state->label())
                    ->sortable(),
                TextColumn::make('value')
                    ->label(__('coupons.fields.value'))
                    ->formatStateUsing(function (int $state, Coupon $record): string {
                        if ($record->type === CouponTypeEnum::Percentage) {
                            return $state.'%';
                        }

                        return $record->currency?->format($state) ?? number_format($state);
                    })
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('currency')
                    ->label(__('coupons.fields.currency'))
                    ->placeholder('—')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('used_count')
                    ->label(__('coupons.fields.used_count'))
                    ->formatStateUsing(function (int $state, Coupon $record): string {
                        $limit = $record->usage_limit;

                        return $limit === null
                            ? (string) $state
                            : $state.'/'.$limit;
                    })
                    ->alignEnd()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('coupons.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('coupons.fields.starts_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label(__('coupons.fields.expires_at'))
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('coupons.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label(__('coupons.filters.type'))
                    ->options(CouponTypeEnum::class),
                TernaryFilter::make('is_active')
                    ->label(__('coupons.filters.active'))
                    ->placeholder(__('coupons.placeholders.filter_all'))
                    ->trueLabel(__('coupons.filters.active_only'))
                    ->falseLabel(__('coupons.filters.inactive_only')),
                SelectFilter::make('currency')
                    ->label(__('coupons.filters.currency'))
                    ->options(CurrencyEnum::class),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('coupons.actions.edit')),
            ])
            ->toolbarActions([])
            ->emptyStateIcon(Heroicon::OutlinedTicket)
            ->emptyStateHeading(__('coupons.empty.heading'))
            ->emptyStateDescription(__('coupons.empty.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('coupons.actions.create'))
                    ->icon(Heroicon::Plus),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
