<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups\Schemas;

use App\Enums\Localization\LocaleEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

final class PromotionalPopupForm
{
    public static function configure(Schema $schema): Schema
    {
        $translationTabs = collect(LocaleEnum::cases())->map(function (LocaleEnum $locale): Tab {
            $isDefault = ($locale === LocaleEnum::Es);

            return Tab::make($locale->label())
                ->badge($isDefault ? __('promotional_popups.badges.primary') : null)
                ->schema([
                    TextInput::make("title.{$locale->value}")
                        ->label(__('promotional_popups.fields.title')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? '¡Oferta Especial de Bienvenida!' : 'Special Welcome Offer!')
                        ->required($isDefault)
                        ->nullable(! $isDefault)
                        ->maxLength(255),

                    TextInput::make("subtitle.{$locale->value}")
                        ->label(__('promotional_popups.fields.subtitle')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? 'Recibe un descuento exclusivo en tu primera compra.' : 'Get an exclusive discount on your first order.')
                        ->nullable()
                        ->maxLength(500),

                    TextInput::make("cta_text.{$locale->value}")
                        ->label(__('promotional_popups.fields.cta_text')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? 'Aprovechar Descuento' : 'Claim Discount')
                        ->nullable()
                        ->maxLength(100),
                ]);
        })->values()->all();

        return $schema
            ->components([
                Section::make(__('promotional_popups.sections.content'))
                    ->description(__('promotional_popups.sections.content_description'))
                    ->schema([
                        Tabs::make('Translations')
                            ->tabs($translationTabs)
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label(__('promotional_popups.fields.image'))
                            ->helperText(__('promotional_popups.fields.image_helper'))
                            ->image()
                            ->disk('public')
                            ->directory('popups')
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('cta_url')
                            ->label(__('promotional_popups.fields.cta_url'))
                            ->helperText(__('promotional_popups.fields.cta_url_helper'))
                            ->placeholder('https://... o /tienda')
                            ->nullable()
                            ->maxLength(2048)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('promotional_popups.sections.coupon'))
                    ->description(__('promotional_popups.sections.coupon_description'))
                    ->schema([
                        Select::make('coupon_id')
                            ->label(__('promotional_popups.fields.coupon'))
                            ->helperText(__('promotional_popups.fields.coupon_helper'))
                            ->relationship('coupon', 'code')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('promotional_popups.sections.schedule'))
                    ->description(__('promotional_popups.sections.schedule_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('delay_seconds')
                                ->label(__('promotional_popups.fields.delay_seconds'))
                                ->helperText(__('promotional_popups.fields.delay_seconds_helper'))
                                ->numeric()
                                ->default(5)
                                ->minValue(1)
                                ->maxValue(60)
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('sort_order')
                                ->label(__('promotional_popups.fields.sort_order'))
                                ->helperText(__('promotional_popups.fields.sort_order_helper'))
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label(__('promotional_popups.fields.is_active'))
                                ->default(true)
                                ->columnSpanFull(),

                            DateTimePicker::make('starts_at')
                                ->label(__('promotional_popups.fields.starts_at'))
                                ->nullable()
                                ->columnSpan(1),

                            DateTimePicker::make('ends_at')
                                ->label(__('promotional_popups.fields.ends_at'))
                                ->nullable()
                                ->afterOrEqual('starts_at')
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
