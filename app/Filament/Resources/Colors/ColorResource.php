<?php

declare(strict_types=1);

namespace App\Filament\Resources\Colors;

use App\Filament\Resources\Colors\Pages\CreateColor;
use App\Filament\Resources\Colors\Pages\EditColor;
use App\Filament\Resources\Colors\Pages\ListColors;
use App\Filament\Support\NameSlugInputs;
use App\Models\Color;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ColorResource extends Resource
{
    protected static ?string $model = Color::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.catalog');
    }

    public static function getModelLabel(): string
    {
        return __('colors.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('colors.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('colors.section.details'))
                    ->description(__('colors.section.details_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ...array_map(
                                    static fn ($field) => $field->columnSpan(1),
                                    NameSlugInputs::make(
                                        modelClass: Color::class,
                                        namePlaceholder: __('colors.placeholders.name'),
                                        slugPlaceholder: __('colors.placeholders.slug'),
                                    ),
                                ),
                            ]),
                        Grid::make(3)
                            ->schema([
                                ColorPicker::make('hex_code')
                                    ->label(__('colors.fields.hex_code'))
                                    ->helperText(__('colors.helpers.hex_code'))
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('sort_order')
                                    ->label(__('colors.fields.sort_order'))
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(1),
                                Toggle::make('is_active')
                                    ->label(__('colors.fields.is_active'))
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('hex_code')
                    ->label(__('colors.fields.hex_code'))
                    ->copyable()
                    ->copyMessage(__('filament_support.copied')),
                TextColumn::make('name')
                    ->label(__('colors.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('slug')
                    ->label(__('colors.fields.slug'))
                    ->searchable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variants_count')
                    ->label(__('colors.fields.variants_count'))
                    ->counts('variants')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label(__('colors.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('colors.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order', 'asc')
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('colors.fields.is_active'))
                    ->options([
                        '1' => __('filament_support.active'),
                        '0' => __('filament_support.inactive'),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('colors.actions.edit')),
                DeleteAction::make()
                    ->label(__('colors.actions.delete')),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label(__('colors.actions.create'))
                    ->icon(Heroicon::Plus),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('colors.actions.delete_selected')),
                ]),
            ])
            ->emptyStateHeading(__('colors.empty.heading'))
            ->emptyStateDescription(__('colors.empty.description'));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColors::route('/'),
            'create' => CreateColor::route('/create'),
            'edit' => EditColor::route('/{record}/edit'),
        ];
    }
}
