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
use Filament\Forms\Components\FileUpload;
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

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.catalog');
    }

    public static function getModelLabel(): string
    {
        return __('categories.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('categories.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('categories.section.details'))
                    ->description(__('categories.section.details_description'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ...array_map(
                                    static fn ($field) => $field->columnSpan(1),
                                    NameSlugInputs::make(
                                        modelClass: Category::class,
                                        namePlaceholder: __('categories.placeholders.name'),
                                        slugPlaceholder: __('categories.placeholders.slug'),
                                    ),
                                ),
                            ]),
                        Select::make('parent_id')
                            ->label(__('categories.fields.parent_category'))
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
                            ->helperText(__('categories.helpers.parent_optional')),
                        FileUpload::make('image_path')
                            ->label(__('categories.fields.image'))
                            ->disk('public')
                            ->directory('categories')
                            ->visibility('public')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->nullable()
                            ->helperText(__('categories.helpers.image'))
                            ->columnSpanFull(),
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
                    ->label(__('categories.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->description(fn (Category $record): ?string => $record->slug),
                TextColumn::make('parent.name')
                    ->label(__('categories.fields.parent'))
                    ->placeholder(__('categories.placeholders.root'))
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('products_count')
                    ->counts('products')
                    ->label(__('categories.fields.products_count'))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('sort_order')
                    ->label(__('categories.fields.sort_order'))
                    ->numeric()
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('categories.fields.created_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('categories.fields.updated_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('parent_id')
                    ->label(__('categories.fields.parent_category'))
                    ->relationship('parent', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('categories.actions.edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label(__('categories.actions.delete_selected'))
                        ->requiresConfirmation()
                        ->modalHeading(__('categories.modals.delete_bulk_heading'))
                        ->modalDescription(__('categories.modals.delete_bulk_description'))
                        ->modalSubmitActionLabel(__('categories.actions.confirm_delete')),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading(__('categories.empty.heading'))
            ->emptyStateDescription(__('categories.empty.description'))
            ->emptyStateActions([
                CreateAction::make()
                    ->label(__('categories.actions.create'))
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
