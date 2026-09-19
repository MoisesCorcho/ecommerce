<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups;

use App\Filament\Resources\PromotionalPopups\Pages\CreatePromotionalPopup;
use App\Filament\Resources\PromotionalPopups\Pages\EditPromotionalPopup;
use App\Filament\Resources\PromotionalPopups\Pages\ListPromotionalPopups;
use App\Filament\Resources\PromotionalPopups\Schemas\PromotionalPopupForm;
use App\Filament\Resources\PromotionalPopups\Schemas\PromotionalPopupsTable;
use App\Models\PromotionalPopup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PromotionalPopupResource extends Resource
{
    protected static ?string $model = PromotionalPopup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.marketing');
    }

    public static function getModelLabel(): string
    {
        return __('promotional_popups.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('promotional_popups.model.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('promotional_popups.navigation.label');
    }

    public static function form(Schema $schema): Schema
    {
        return PromotionalPopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PromotionalPopupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotionalPopups::route('/'),
            'create' => CreatePromotionalPopup::route('/create'),
            'edit' => EditPromotionalPopup::route('/{record}/edit'),
        ];
    }
}
