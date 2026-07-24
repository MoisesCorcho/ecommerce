<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Schemas;

use App\Enums\Commerce\CurrencyEnum;
use App\Enums\Coupons\CouponTypeEnum;
use App\Models\Coupon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('coupons.sections.identity'))
                    ->description(__('coupons.sections.identity_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('code')
                                ->label(__('coupons.fields.code'))
                                ->placeholder(__('coupons.placeholders.code'))
                                ->helperText(__('coupons.helpers.code'))
                                ->required()
                                ->maxLength(32)
                                ->unique(ignoreRecord: true)
                                ->extraInputAttributes(['class' => 'uppercase'])
                                ->columnSpan(1),
                            Toggle::make('is_active')
                                ->label(__('coupons.fields.is_active'))
                                ->helperText(__('coupons.helpers.is_active'))
                                ->default(true)
                                ->inline(false)
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('coupons.sections.discount'))
                    ->description(__('coupons.sections.discount_description'))
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('type')
                                ->label(__('coupons.fields.type'))
                                ->options(CouponTypeEnum::class)
                                ->required()
                                ->live()
                                ->disabled(fn (?Coupon $record): bool => self::hasRedemptions($record))
                                ->dehydrated()
                                ->columnSpan(1),
                            TextInput::make('value')
                                ->label(__('coupons.fields.value'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(fn (Get $get): ?int => $get('type') === CouponTypeEnum::Percentage->value
                                    || $get('type') === CouponTypeEnum::Percentage
                                    ? 100
                                    : null)
                                ->helperText(fn (Get $get): string => (
                                    $get('type') === CouponTypeEnum::Fixed->value
                                    || $get('type') === CouponTypeEnum::Fixed
                                )
                                    ? __('coupons.helpers.value_fixed')
                                    : __('coupons.helpers.value_percentage'))
                                ->disabled(fn (?Coupon $record): bool => self::hasRedemptions($record))
                                ->dehydrated()
                                ->columnSpan(1),
                            Select::make('currency')
                                ->label(__('coupons.fields.currency'))
                                ->options(CurrencyEnum::class)
                                ->nullable()
                                ->visible(fn (Get $get): bool => $get('type') === CouponTypeEnum::Fixed->value
                                    || $get('type') === CouponTypeEnum::Fixed)
                                ->required(fn (Get $get): bool => $get('type') === CouponTypeEnum::Fixed->value
                                    || $get('type') === CouponTypeEnum::Fixed)
                                ->helperText(fn (Get $get): string => (
                                    $get('type') === CouponTypeEnum::Fixed->value
                                    || $get('type') === CouponTypeEnum::Fixed
                                )
                                    ? __('coupons.helpers.currency_fixed')
                                    : __('coupons.helpers.currency_percentage'))
                                ->disabled(fn (?Coupon $record): bool => self::hasRedemptions($record))
                                ->dehydrated()
                                ->columnSpan(1),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('min_order_amount')
                                ->label(__('coupons.fields.min_order_amount'))
                                ->numeric()
                                ->minValue(1)
                                ->nullable()
                                ->helperText(__('coupons.helpers.min_order'))
                                ->columnSpan(1),
                            Select::make('min_order_currency')
                                ->label(__('coupons.fields.min_order_currency'))
                                ->options(CurrencyEnum::class)
                                ->nullable()
                                ->visible(fn (Get $get): bool => filled($get('min_order_amount')))
                                ->required(fn (Get $get): bool => filled($get('min_order_amount')))
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('coupons.sections.limits'))
                    ->description(__('coupons.sections.limits_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('usage_limit')
                                ->label(__('coupons.fields.usage_limit'))
                                ->numeric()
                                ->minValue(1)
                                ->nullable()
                                ->helperText(__('coupons.helpers.usage_limit'))
                                ->placeholder(__('coupons.placeholders.unlimited'))
                                ->columnSpan(1),
                            TextInput::make('usage_limit_per_user')
                                ->label(__('coupons.fields.usage_limit_per_user'))
                                ->numeric()
                                ->minValue(1)
                                ->nullable()
                                ->helperText(__('coupons.helpers.usage_limit_per_user'))
                                ->placeholder(__('coupons.placeholders.unlimited'))
                                ->columnSpan(1),
                            DateTimePicker::make('starts_at')
                                ->label(__('coupons.fields.starts_at'))
                                ->nullable()
                                ->seconds(false)
                                ->columnSpan(1),
                            DateTimePicker::make('expires_at')
                                ->label(__('coupons.fields.expires_at'))
                                ->nullable()
                                ->seconds(false)
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function hasRedemptions(?Coupon $record): bool
    {
        if ($record === null || ! $record->exists) {
            return false;
        }

        return $record->redemptions()->exists();
    }
}
