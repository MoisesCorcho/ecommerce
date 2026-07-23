<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\RelationManagers\RedemptionsRelationManager;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Schemas\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.commerce');
    }

    public static function getModelLabel(): string
    {
        return __('coupons.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('coupons.model.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('coupons.navigation.label');
    }

    public static function form(Schema $schema): Schema
    {
        return CouponForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('redemptions');
    }

    public static function getRelations(): array
    {
        return [
            RedemptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }

    /**
     * No hard-delete from panel (D30 / R13).
     */
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
