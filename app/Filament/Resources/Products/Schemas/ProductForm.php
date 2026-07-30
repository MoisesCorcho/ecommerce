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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make(__('products.tabs.product'))
                    ->tabs([
                        Tab::make(__('products.tabs.details'))
                            ->icon('heroicon-o-information-circle')
                            ->schema(self::detailsSchema()),
                        Tab::make(__('products.tabs.variants_prices'))
                            ->icon('heroicon-o-squares-2x2')
                            ->schema(self::variantsSchema()),
                        Tab::make(__('products.tabs.images'))
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
            Section::make(__('products.section.identity'))
                ->description(__('products.section.identity_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            ...array_map(
                                static fn ($field) => $field->columnSpan(1),
                                NameSlugInputs::make(
                                    modelClass: Product::class,
                                    namePlaceholder: __('products.placeholders.name'),
                                    slugPlaceholder: __('products.placeholders.slug'),
                                ),
                            ),
                        ]),
                    Select::make('category_id')
                        ->label(__('products.fields.category'))
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->placeholder(__('products.placeholders.no_category'))
                        ->helperText(__('products.helpers.category_optional')),
                    Textarea::make('description')
                        ->label(__('products.fields.description'))
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder(__('products.placeholders.description')),
                ]),
            Section::make(__('products.section.attributes'))
                ->description(__('products.section.attributes_description'))
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('material')
                                ->label(__('products.fields.material'))
                                ->maxLength(255)
                                ->placeholder(__('products.placeholders.material')),
                            TextInput::make('dimensions')
                                ->label(__('products.fields.dimensions'))
                                ->maxLength(255)
                                ->placeholder(__('products.placeholders.dimensions')),
                        ]),
                    Grid::make(2)
                        ->schema([
                            Toggle::make('is_preorder')
                                ->label(__('products.fields.is_preorder'))
                                ->helperText(__('products.helpers.is_preorder'))
                                ->default(false)
                                ->inline(false),
                            Toggle::make('is_active')
                                ->label(__('products.fields.is_active'))
                                ->helperText(__('products.helpers.is_active'))
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
            Section::make(__('products.section.variants'))
                ->description(__('products.section.variants_description'))
                ->schema([
                    Repeater::make('variants')
                        ->label(__('products.fields.variants'))
                        ->schema([
                            Hidden::make('id'),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('sku')
                                        ->label(__('products.fields.sku'))
                                        ->placeholder(__('products.placeholders.sku'))
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
                                                    $fail(__('products.validation.sku_unique', ['sku' => $value]));
                                                }
                                            };
                                        }),
                                    TextInput::make('color')
                                        ->label(__('products.fields.color'))
                                        ->maxLength(255)
                                        ->placeholder(__('products.placeholders.color')),
                                    TextInput::make('size')
                                        ->label(__('products.fields.size'))
                                        ->maxLength(255)
                                        ->placeholder(__('products.placeholders.size')),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('stock')
                                        ->label(__('products.fields.stock'))
                                        ->numeric()
                                        ->integer()
                                        ->minValue(0)
                                        ->default(0)
                                        ->required(),
                                    Toggle::make('is_active')
                                        ->label(__('products.fields.variant_active'))
                                        ->helperText(__('products.helpers.variant_active'))
                                        ->default(true)
                                        ->inline(false),
                                ]),
                            Repeater::make('prices')
                                ->label(__('products.fields.prices'))
                                ->schema([
                                    Hidden::make('id'),
                                    Select::make('currency')
                                        ->label(__('products.fields.currency'))
                                        ->options(CurrencyEnum::class)
                                        ->required()
                                        ->distinct()
                                        ->live(onBlur: true)
                                        ->native(false),
                                    TextInput::make('price')
                                        ->label(__('products.fields.price'))
                                        ->helperText(__('products.helpers.price_units'))
                                        ->numeric()
                                        ->integer()
                                        ->minValue(0)
                                        ->required()
                                        ->placeholder(__('products.placeholders.price')),
                                    TextInput::make('compare_at_price')
                                        ->label(__('products.fields.compare_at_price'))
                                        ->helperText(__('products.helpers.compare_at_price'))
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
                                ->addActionLabel(__('products.actions.add_price'))
                                ->itemLabel(function (array $state): ?string {
                                    $currency = $state['currency'] ?? null;
                                    if ($currency instanceof CurrencyEnum) {
                                        $code = $currency->value;
                                    } else {
                                        $code = is_string($currency) ? $currency : null;
                                    }

                                    if ($code === null) {
                                        return __('products.item_labels.price');
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
                        ->addActionLabel(__('products.actions.add_variant'))
                        ->itemLabel(function (array $state): ?string {
                            $sku = $state['sku'] ?? null;
                            if (! is_string($sku) || $sku === '') {
                                return __('products.item_labels.new_variant');
                            }

                            $active = (bool) ($state['is_active'] ?? true);

                            return $active
                                ? $sku
                                : __('products.item_labels.variant_inactive', ['sku' => $sku]);
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
            Section::make(__('products.section.gallery'))
                ->description(__('products.section.gallery_description'))
                ->schema([
                    Repeater::make('images')
                        ->label(__('products.fields.images'))
                        ->schema([
                            Hidden::make('id'),
                            FileUpload::make('path')
                                ->label(__('products.fields.file'))
                                ->disk('public')
                                ->directory('products')
                                ->visibility('public')
                                ->image()
                                ->imagePreviewHeight('120')
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(5120)
                                ->helperText(__('products.helpers.image_file'))
                                ->required()
                                ->columnSpanFull(),
                            Select::make('product_variant_id')
                                ->label(__('products.fields.image_variant'))
                                ->options(function (Get $get): array {
                                    /** @var array<int|string, array<string, mixed>> $variants */
                                    $variants = $get('../../variants') ?? [];

                                    return collect($variants)
                                        ->filter(fn (array $variant): bool => filled($variant['id'] ?? null))
                                        ->mapWithKeys(fn (array $variant): array => [
                                            (int) $variant['id'] => self::variantOptionLabel($variant),
                                        ])
                                        ->all();
                                })
                                ->nullable()
                                ->native(false)
                                ->helperText(__('products.helpers.image_variant'))
                                ->columnSpanFull(),
                            ExclusiveToggleInRepeater::make(
                                name: 'is_primary',
                                label: __('products.fields.primary'),
                                helperText: __('products.helpers.primary_image'),
                                repeaterField: 'images',
                            ),
                        ])
                        ->defaultItems(0)
                        ->grid([
                            'default' => 1,
                            'md' => 2,
                            'xl' => 3,
                        ])
                        ->collapsible()
                        ->cloneable(false)
                        ->reorderable()
                        ->reorderableWithButtons()
                        ->addActionLabel(__('products.actions.add_image'))
                        ->itemLabel(function (array $state): ?string {
                            if (($state['is_primary'] ?? false) === true) {
                                return __('products.item_labels.primary');
                            }

                            $path = $state['path'] ?? null;
                            if (is_string($path) && $path !== '') {
                                return basename($path);
                            }

                            return __('products.item_labels.new_image');
                        })
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    private static function variantOptionLabel(array $variant): string
    {
        $sku = (string) ($variant['sku'] ?? '');

        $descriptor = collect([$variant['color'] ?? null, $variant['size'] ?? null])
            ->filter(fn (mixed $value): bool => is_string($value) && $value !== '')
            ->implode(' / ');

        return $descriptor === '' ? $sku : "{$sku} — {$descriptor}";
    }
}
