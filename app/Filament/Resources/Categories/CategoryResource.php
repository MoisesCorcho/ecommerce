<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Support\NameSlugInputs;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'categoría';

    protected static ?string $pluralModelLabel = 'categorías';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la categoría')
                    ->description('Organiza el catálogo en una jerarquía opcional. El slug se usa en URLs y listados. El orden se define arrastrando filas en el listado.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ...array_map(
                                    static fn ($field) => $field->columnSpan(1),
                                    NameSlugInputs::make(
                                        modelClass: Category::class,
                                        namePlaceholder: 'Ej. Bolsos de mano',
                                        slugPlaceholder: 'bolsos-de-mano',
                                    ),
                                ),
                            ]),
                        Select::make('parent_id')
                            ->label('Categoría padre')
                            ->relationship(
                                name: 'parent',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, ?Category $record): Builder {
                                    if ($record?->exists) {
                                        $query->whereKeyNot($record->getKey());
                                    }

                                    return $query->orderBy('name');
                                },
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opcional. Deja vacío para una categoría de nivel raíz.'),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Category $record): ?string => $record->slug),
                TextColumn::make('parent.name')
                    ->label('Padre')
                    ->placeholder('Raíz')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Productos')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('sort_order')
                    ->label('Orden')
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('parent_id')
                    ->label('Categoría padre')
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionadas')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar categorías')
                        ->modalDescription('Los productos asociados quedarán sin categoría. Esta acción no se puede deshacer desde aquí.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('No hay categorías todavía')
            ->emptyStateDescription('Crea la primera categoría para organizar el catálogo de productos.')
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Nueva categoría')
                    ->icon(Heroicon::Plus),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with('parent')
            ->withCount('products');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
