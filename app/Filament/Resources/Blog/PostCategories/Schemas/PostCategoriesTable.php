<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\PostCategories\Schemas;

use App\Models\PostCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

final class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('blog.categories.fields.name'))
                    ->state(fn (PostCategory $record): string => $record->getLocalizedName())
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('name', 'like', "%{$search}%");
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy('name->es', $direction);
                    }),

                TextColumn::make('slug')
                    ->label(__('blog.categories.fields.slug'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('posts_count')
                    ->label(__('blog.categories.fields.posts_count'))
                    ->counts('posts')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label(__('blog.categories.fields.is_active')),

                TextColumn::make('sort_order')
                    ->label(__('blog.categories.fields.sort_order'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('blog.categories.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
