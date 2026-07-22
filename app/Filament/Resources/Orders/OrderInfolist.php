<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders;

use App\Enums\Orders\OrderStatusEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('orders.sections.summary'))
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('orders.fields.order_number')),
                        TextEntry::make('status')
                            ->label(__('orders.fields.status'))
                            ->badge()
                            ->formatStateUsing(fn (OrderStatusEnum $state): string => $state->label()),
                        TextEntry::make('email')
                            ->label(__('orders.fields.email')),
                        TextEntry::make('currency')
                            ->label(__('orders.fields.currency')),
                        TextEntry::make('subtotal')
                            ->label(__('orders.fields.subtotal'))
                            ->numeric(),
                        TextEntry::make('shipping_cost')
                            ->label(__('orders.fields.shipping_cost'))
                            ->numeric(),
                        TextEntry::make('discount')
                            ->label(__('orders.fields.discount'))
                            ->numeric(),
                        TextEntry::make('tax_amount')
                            ->label(__('orders.fields.tax_amount'))
                            ->numeric(),
                        TextEntry::make('total')
                            ->label(__('orders.fields.total'))
                            ->numeric()
                            ->weight('bold'),
                        TextEntry::make('customer_notes')
                            ->label(__('orders.fields.customer_notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('orders.sections.shipping'))
                    ->schema([
                        TextEntry::make('shipping_full_name')
                            ->label(__('orders.fields.shipping_full_name')),
                        TextEntry::make('shipping_phone')
                            ->label(__('orders.fields.shipping_phone')),
                        TextEntry::make('shipping_address_line_1')
                            ->label(__('orders.fields.shipping_address_line_1')),
                        TextEntry::make('shipping_address_line_2')
                            ->label(__('orders.fields.shipping_address_line_2'))
                            ->placeholder('—'),
                        TextEntry::make('shipping_city')
                            ->label(__('orders.fields.shipping_city')),
                        TextEntry::make('shipping_state')
                            ->label(__('orders.fields.shipping_state')),
                        TextEntry::make('shipping_country')
                            ->label(__('orders.fields.shipping_country')),
                        TextEntry::make('shipping_postal_code')
                            ->label(__('orders.fields.shipping_postal_code'))
                            ->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make(__('orders.sections.items'))
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label(__('orders.fields.items'))
                            ->schema([
                                TextEntry::make('product_name')
                                    ->label(__('orders.fields.product_name')),
                                TextEntry::make('variant_label')
                                    ->label(__('orders.fields.variant_label'))
                                    ->placeholder('—'),
                                TextEntry::make('sku')
                                    ->label(__('orders.fields.sku'))
                                    ->placeholder('—'),
                                TextEntry::make('unit_price')
                                    ->label(__('orders.fields.unit_price'))
                                    ->numeric(),
                                TextEntry::make('quantity')
                                    ->label(__('orders.fields.quantity')),
                            ])
                            ->columns(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
