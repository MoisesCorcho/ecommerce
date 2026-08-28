<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups\Schemas;

use App\Models\PromotionalPopup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

final class PromotionalPopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label(__('promotional_popups.fields.image'))
                    ->disk('public')
                    ->circular(false)
                    ->height(40)
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label(__('promotional_popups.fields.title'))
                    ->state(fn (PromotionalPopup $record): string => $record->getLocalizedTitle())
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('title', 'like', "%{$search}%");
                    })
                    ->limit(40),

                TextColumn::make('coupon.code')
                    ->label(__('promotional_popups.fields.coupon'))
                    ->placeholder('-')
                    ->badge()
                    ->color('info'),

                ToggleColumn::make('is_active')
                    ->label(__('promotional_popups.fields.is_active')),

                TextColumn::make('delay_seconds')
                    ->label(__('promotional_popups.fields.delay_seconds'))
                    ->suffix('s')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label(__('promotional_popups.fields.sort_order'))
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label(__('promotional_popups.fields.starts_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label(__('promotional_popups.fields.ends_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
