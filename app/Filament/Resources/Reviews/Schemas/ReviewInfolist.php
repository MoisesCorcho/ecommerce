<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('reviews.sections.details'))
                    ->schema([
                        TextEntry::make('product.name')
                            ->label(__('reviews.fields.product')),
                        TextEntry::make('user.name')
                            ->label(__('reviews.fields.user'))
                            ->formatStateUsing(function (?string $state, Review $record): string {
                                $email = $record->user?->email;

                                return $email !== null ? "{$state} ({$email})" : (string) $state;
                            }),
                        TextEntry::make('rating')
                            ->label(__('reviews.fields.rating'))
                            ->formatStateUsing(fn (int $state): string => (string) $state.' / 5'),
                        IconEntry::make('is_approved')
                            ->label(__('reviews.fields.is_approved'))
                            ->boolean(),
                        IconEntry::make('is_verified_purchase')
                            ->label(__('reviews.fields.is_verified_purchase'))
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label(__('reviews.fields.created_at'))
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label(__('reviews.fields.updated_at'))
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
                Section::make(__('reviews.sections.content'))
                    ->schema([
                        TextEntry::make('comment')
                            ->label(__('reviews.fields.comment'))
                            ->placeholder(__('reviews.empty.no_comment'))
                            ->columnSpanFull()
                            ->prose(),
                    ]),
            ]);
    }
}
