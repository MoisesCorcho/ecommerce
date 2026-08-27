<?php

declare(strict_types=1);

namespace App\Filament\Resources\Announcements\Schemas;

use App\Models\Announcement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

final class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')
                    ->label(__('announcements.fields.text'))
                    ->state(fn (Announcement $record): string => $record->getLocalizedText())
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('text', 'like', "%{$search}%");
                    })
                    ->limit(50),

                TextColumn::make('url')
                    ->label(__('announcements.fields.url'))
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_active')
                    ->label(__('announcements.fields.is_active')),

                TextColumn::make('sort_order')
                    ->label(__('announcements.fields.sort_order'))
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label(__('announcements.fields.starts_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label(__('announcements.fields.ends_at'))
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
