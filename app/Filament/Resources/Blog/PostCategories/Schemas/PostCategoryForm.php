<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\PostCategories\Schemas;

use App\Enums\Localization\LocaleEnum;
use App\Models\PostCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $translationTabs = collect(LocaleEnum::cases())->map(function (LocaleEnum $locale): Tab {
            $isDefault = ($locale === LocaleEnum::Es);

            return Tab::make($locale->label())
                ->badge($isDefault ? __('blog.posts.badges.primary') : null)
                ->schema([
                    TextInput::make("name.{$locale->value}")
                        ->label(__('blog.categories.fields.name')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? 'Tendencias de Moda' : 'Fashion Trends')
                        ->required($isDefault)
                        ->nullable(! $isDefault)
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) use ($locale): void {
                            if ($locale !== LocaleEnum::Es) {
                                return;
                            }
                            $currentSlug = (string) ($get('slug') ?? '');
                            $previousAutoSlug = Str::slug((string) $old);
                            if ($currentSlug === '' || $currentSlug === $previousAutoSlug) {
                                $set('slug', filled($state) ? Str::slug($state) : '');
                            }
                        }),

                    Textarea::make("description.{$locale->value}")
                        ->label(__('blog.categories.fields.description')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? 'Artículos y guías sobre tendencias...' : 'Articles and styling guides...')
                        ->nullable()
                        ->rows(3),
                ]);
        })->values()->all();

        return $schema
            ->components([
                Section::make(__('blog.categories.sections.content'))
                    ->description(__('blog.categories.sections.content_description'))
                    ->schema([
                        Tabs::make('Translations')
                            ->tabs($translationTabs)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label(__('blog.categories.fields.slug'))
                            ->helperText(__('blog.categories.fields.slug_helper'))
                            ->required()
                            ->maxLength(180)
                            ->unique(table: PostCategory::class, column: 'slug', ignoreRecord: true)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('blog.categories.sections.settings'))
                    ->description(__('blog.categories.sections.settings_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sort_order')
                                ->label(__('blog.categories.fields.sort_order'))
                                ->helperText(__('blog.categories.fields.sort_order_helper'))
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label(__('blog.categories.fields.is_active'))
                                ->default(true)
                                ->columnSpan(1),
                        ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
