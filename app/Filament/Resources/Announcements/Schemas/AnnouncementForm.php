<?php

declare(strict_types=1);

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('announcements.sections.content'))
                    ->description(__('announcements.sections.content_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('text.es')
                                ->label(__('announcements.fields.text_es'))
                                ->placeholder('¡Envío gratis en compras mayores a $150.000!')
                                ->required()
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('text.en')
                                ->label(__('announcements.fields.text_en'))
                                ->placeholder('Free shipping on orders over $150,000!')
                                ->nullable()
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('url')
                                ->label(__('announcements.fields.url'))
                                ->helperText(__('announcements.fields.url_helper'))
                                ->placeholder('https://... o /tienda')
                                ->nullable()
                                ->maxLength(2048)
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('announcements.sections.schedule'))
                    ->description(__('announcements.sections.schedule_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_active')
                                ->label(__('announcements.fields.is_active'))
                                ->default(true)
                                ->columnSpan(1),

                            TextInput::make('sort_order')
                                ->label(__('announcements.fields.sort_order'))
                                ->helperText(__('announcements.fields.sort_order_helper'))
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->columnSpan(1),

                            DateTimePicker::make('starts_at')
                                ->label(__('announcements.fields.starts_at'))
                                ->nullable()
                                ->columnSpan(1),

                            DateTimePicker::make('ends_at')
                                ->label(__('announcements.fields.ends_at'))
                                ->nullable()
                                ->afterOrEqual('starts_at')
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
