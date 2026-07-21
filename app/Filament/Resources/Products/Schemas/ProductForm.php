<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\Commerce\CurrencyEnum;
use App\Filament\Support\ExclusiveToggleInRepeater;
use App\Filament\Support\NameSlugInputs;
use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Producto')
                    ->tabs([
                        Tab::make('Datos')
                            ->icon('heroicon-o-information-circle')
                            ->schema(self::detailsSchema()),
                        Tab::make('Variantes y precios')
                            ->icon('heroicon-o-squares-2x2')
                            ->schema(self::variantsSchema()),
                        Tab::make('Imágenes')
                            ->icon('heroicon-o-photo')
                            ->schema(self::imagesSchema()),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    /**
     * @return array<int, Section>
     */
    private static function detailsSchema(): array
    {
        return [
            Section::make('Identidad del producto')
                ->description('Nombre, categoría y visibilidad en el catálogo. Publicar exige al menos una variante activa con precio.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ...array_map(
                                static fn ($field) => $field->columnSpan(1),
                                NameSlugInputs::make(
                                    modelClass: Product::class,
                                    namePlaceholder: 'Ej. Bolso Honey',
                                    slugPlaceholder: 'bolso-honey',
                                ),
                            ),
                        ]),
                    Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder('Sin categoría')
                        ->helperText('Opcional. Puedes asignarla o cambiarla más tarde.'),
                    Textarea::make('description')
                        ->label('Descripción')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Materiales, uso recomendado, detalles de la pieza…'),
                ]),
            Section::make('Atributos y estado')
                ->description('Detalles comerciales y flags de publicación.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('material')
                                ->label('Material')
                                ->maxLength(255)
                                ->placeholder('Cuero, lona, etc.'),
                            TextInput::make('dimensions')
                                ->label('Dimensiones')
                                ->maxLength(255)
                                ->placeholder('30 × 20 × 10 cm'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_preorder')
                                ->label('Preventa')
                                ->helperText('Marca si el producto se vende antes de stock físico.')
                                ->default(false)
                                ->inline(false),
                            Toggle::make('is_active')
                                ->label('Publicado')
                                ->helperText('Requiere ≥1 variante activa con ≥1 precio (cualquier moneda). Si no se cumple, el guardado fallará con un mensaje claro.')
                                ->default(false)
                                ->inline(false),
                        ]),
                ])
                ->collapsed(false),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function variantsSchema(): array
    {
        return [
            Section::make('Variantes vendibles')
                ->description('Cada variante es una opción de compra (SKU). Los precios son enteros: COP en pesos; EUR en centavos.')
                ->schema([
                    Repeater::make('variants')
                        ->label('Variantes')
                        ->schema([
                            Hidden::make('id'),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sku')
                                        ->label('SKU')
                                        ->placeholder('LHB-HONEY-01')
                                        ->required()
                                        ->maxLength(255)
                                        ->distinct()
                                        ->live(onBlur: true)
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
                                        ->maxLength(255)
                                        ->placeholder('Negro'),
                                    TextInput::make('size')
                                        ->label('Talla / tamaño')
                                        ->maxLength(255)
                                        ->placeholder('Única, M, 30cm…'),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('stock')
                                        ->label('Stock')
                                        ->numeric()
                                        ->integer()
                                        ->minValue(0)
                                        ->default(0)
                                        ->required(),
                                    Toggle::make('is_active')
                                        ->label('Variante activa')
                                        ->helperText('Solo las activas cuentan para publicar el producto.')
                                        ->default(true)
                                        ->inline(false),
                                ]),
                            Repeater::make('prices')
                                ->label('Precios por moneda')
                                ->schema([
                                    Hidden::make('id'),
                                    Select::make('currency')
                                        ->label('Moneda')
                                        ->options(CurrencyEnum::class)
                                        ->required()
                                        ->distinct()
                                        ->live(onBlur: true)
                                        ->native(false),
                                    TextInput::make('price')
                                        ->label('Precio (entero)')
                                        ->helperText('COP: pesos enteros. EUR: centavos (12900 = €129,00).')
                                        ->numeric()
                                        ->integer()
                                        ->minValue(0)
                                        ->required()
                                        ->placeholder('799000'),
                                    TextInput::make('compare_at_price')
                                        ->label('Precio de comparación')
                                        ->helperText('Opcional. Precio “antes” para mostrar descuento.')
                                        ->numeric()
                                        ->integer()
                                        ->minValue(0)
                                        ->nullable(),
                                ])
                                ->columns(3)
                                ->defaultItems(1)
                                ->minItems(0)
                                ->collapsible()
                                ->cloneable()
                                ->addActionLabel('Añadir precio')
                                ->itemLabel(function (array $state): ?string {
                                    $currency = $state['currency'] ?? null;
                                    if ($currency instanceof CurrencyEnum) {
                                        $code = $currency->value;
                                    } else {
                                        $code = is_string($currency) ? $currency : null;
                                    }

                                    if ($code === null) {
                                        return 'Precio';
                                    }

                                    $amount = $state['price'] ?? null;

                                    return is_numeric($amount)
                                        ? "{$code} · {$amount}"
                                        : $code;
                                }),
                        ])
                        ->defaultItems(1)
                        ->minItems(0)
                        ->collapsible()
                        ->cloneable()
                        ->reorderable(false)
                        ->addActionLabel('Añadir variante')
                        ->itemLabel(function (array $state): ?string {
                            $sku = $state['sku'] ?? null;
                            if (! is_string($sku) || $sku === '') {
                                return 'Nueva variante';
                            }

                            $active = (bool) ($state['is_active'] ?? true);

                            return $active ? $sku : "{$sku} (inactiva)";
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function imagesSchema(): array
    {
        return [
            Section::make('Galería del producto')
                ->description('Imágenes en disco público (`products/`). Arrastrá para reordenar. Solo una puede ser primaria.')
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
                                ->imagePreviewHeight('140')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText('JPG, PNG o WebP. Máximo 5 MB.')
                                ->required()
                                ->columnSpanFull(),
                            ExclusiveToggleInRepeater::make(
                                name: 'is_primary',
                                label: 'Imagen primaria',
                                helperText: 'Solo una por producto: al activarla se desmarcan las demás.',
                                repeaterField: 'images',
                            ),
                        ])
                        ->defaultItems(0)
                        ->collapsible()
                        ->cloneable(false)
                        ->reorderable()
                        ->reorderableWithButtons()
                        ->addActionLabel('Añadir imagen')
                        ->itemLabel(function (array $state): ?string {
                            if (($state['is_primary'] ?? false) === true) {
                                return 'Primaria';
                            }

                            $path = $state['path'] ?? null;
                            if (is_string($path) && $path !== '') {
                                return basename($path);
                            }

                            return 'Nueva imagen';
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }
}
