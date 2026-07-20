<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products;

use App\Enums\Commerce\CurrencyEnum;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\ProductVariant;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogo';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'producto';

    protected static ?string $pluralModelLabel = 'productos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del producto')
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoría')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->maxLength(255)
                            ->helperText('Si se deja vacío, se genera a partir del nombre.')
                            ->nullable()
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
                            ->unique(
                                table: Product::class,
                                column: 'slug',
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule->whereNotNull('slug'),
                            ),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpanFull()
                            ->rows(4),
                        TextInput::make('material')
                            ->label('Material')
                            ->maxLength(255),
                        TextInput::make('dimensions')
                            ->label('Dimensiones')
                            ->maxLength(255),
                        Toggle::make('is_preorder')
                            ->label('Preventa')
                            ->default(false),
                        Toggle::make('is_active')
                            ->label('Publicado')
                            ->helperText('Requiere al menos una variante activa con un precio (cualquier moneda).')
                            ->default(false),
                    ])
                    ->columns(2),

                Section::make('Variantes y precios')
                    ->schema([
                        Repeater::make('variants')
                            ->label('Variantes')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->required()
                                    ->maxLength(255)
                                    ->distinct()
                                    ->rule(function (?callable $get) {
                                        return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            if (! is_string($value) || $value === '') {
                                                return;
                                            }

                                            $variantId = $get('id');
                                            $query = ProductVariant::query()->where('sku', $value);
                                            if (filled($variantId)) {
                                                $query->whereKeyNot((int) $variantId);
                                            }

                                            if ($query->exists()) {
                                                $fail("El SKU «{$value}» ya pertenece a otra variante.");
                                            }
                                        };
                                    }),
                                TextInput::make('color')
                                    ->label('Color')
                                    ->maxLength(255),
                                TextInput::make('size')
                                    ->label('Talla')
                                    ->maxLength(255),
                                TextInput::make('stock')
                                    ->label('Stock')
                                    ->numeric()
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Activa')
                                    ->default(true),
                                Repeater::make('prices')
                                    ->label('Precios')
                                    ->schema([
                                        Hidden::make('id'),
                                        Select::make('currency')
                                            ->label('Moneda')
                                            ->options(CurrencyEnum::class)
                                            ->required()
                                            ->distinct(),
                                        TextInput::make('price')
                                            ->label('Precio (entero)')
                                            ->helperText('COP: pesos enteros. EUR: centavos.')
                                            ->numeric()
                                            ->integer()
                                            ->minValue(0)
                                            ->required(),
                                        TextInput::make('compare_at_price')
                                            ->label('Precio comparación')
                                            ->numeric()
                                            ->integer()
                                            ->minValue(0)
                                            ->nullable(),
                                    ])
                                    ->columns(3)
                                    ->defaultItems(1)
                                    ->collapsible()
                                    ->itemLabel(function (array $state): ?string {
                                        $currency = $state['currency'] ?? null;
                                        if ($currency instanceof CurrencyEnum) {
                                            return $currency->value;
                                        }

                                        return is_string($currency) ? $currency : null;
                                    }),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['sku'] ?? null)
                            ->columnSpanFull()
                            ->minItems(0),
                    ]),

                Section::make('Imágenes')
                    ->schema([
                        Repeater::make('images')
                            ->label('Imágenes')
                            ->schema([
                                Hidden::make('id'),
                                FileUpload::make('path')
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->directory('products')
                                    ->visibility('public')
                                    ->image()
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->label('Orden')
                                    ->numeric()
                                    ->integer()
                                    ->default(0),
                                Toggle::make('is_primary')
                                    ->label('Primaria')
                                    ->default(false),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
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
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->placeholder('—')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Publicado')
                    ->boolean(),
                IconColumn::make('is_preorder')
                    ->label('Preventa')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variantes'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Publicado'),
                SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
