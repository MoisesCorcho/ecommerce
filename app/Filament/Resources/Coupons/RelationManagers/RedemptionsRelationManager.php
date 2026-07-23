<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\CouponRedemption;
use BackedEnum;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    protected static string|BackedEnum|null $icon = Heroicon::OutlinedReceiptPercent;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('coupons.relation.redemptions_title');
    }

    public function form(Schema $schema): Schema
    {
        // Read-only relation; no manual redemption creation (D42).
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('order.order_number')
                    ->label(__('coupons.fields.order_number'))
                    ->searchable()
                    ->url(fn (CouponRedemption $record): ?string => $record->order_id
                        ? OrderResource::getUrl('view', ['record' => $record->order_id])
                        : null),
                TextColumn::make('code')
                    ->label(__('coupons.fields.code'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('user.email')
                    ->label(__('coupons.fields.user'))
                    ->placeholder(__('coupons.placeholders.no_user'))
                    ->searchable(),
                TextColumn::make('discount_amount')
                    ->label(__('coupons.fields.discount_amount'))
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('currency')
                    ->label(__('coupons.fields.currency'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('created_at')
                    ->label(__('coupons.fields.redeemed_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->emptyStateIcon(Heroicon::OutlinedReceiptPercent)
            ->emptyStateHeading(__('coupons.empty.redemptions_heading'))
            ->emptyStateDescription(__('coupons.empty.redemptions_description'))
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
