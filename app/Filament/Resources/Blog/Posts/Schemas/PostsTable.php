<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\Posts\Schemas;

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use App\Models\PostCategory;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_image_path')
                    ->label(__('blog.posts.fields.cover_image'))
                    ->disk('public')
                    ->placeholder('-'),

                TextColumn::make('title')
                    ->label(__('blog.posts.fields.title'))
                    ->state(fn (Post $record): string => $record->getLocalizedTitle())
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('title', 'like', "%{$search}%");
                    })
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label(__('blog.posts.fields.category'))
                    ->state(fn (Post $record): ?string => $record->category?->getLocalizedName())
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('blog.posts.fields.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('published_at')
                    ->label(__('blog.posts.fields.published_at'))
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('reading_time')
                    ->label(__('blog.posts.fields.reading_time'))
                    ->state(fn (Post $record): string => "{$record->readingTime()} min")
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('blog.posts.fields.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label(__('blog.posts.fields.status'))
                    ->options(PostStatusEnum::class),

                SelectFilter::make('post_category_id')
                    ->label(__('blog.posts.fields.category'))
                    ->options(function () {
                        return PostCategory::query()
                            ->ordered()
                            ->get()
                            ->mapWithKeys(fn (PostCategory $cat) => [$cat->id => $cat->getLocalizedName()]);
                    }),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
