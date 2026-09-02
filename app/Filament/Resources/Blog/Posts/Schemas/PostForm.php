<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\Posts\Schemas;

use App\Enums\Blog\PostStatusEnum;
use App\Enums\Localization\LocaleEnum;
use App\Models\Post;
use App\Models\PostCategory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        $translationTabs = collect(LocaleEnum::cases())->map(function (LocaleEnum $locale): Tab {
            $isDefault = ($locale === LocaleEnum::Es);

            return Tab::make($locale->label())
                ->badge($isDefault ? __('blog.posts.badges.primary') : null)
                ->schema([
                    TextInput::make("title.{$locale->value}")
                        ->label(__('blog.posts.fields.title')." ({$locale->label()})")
                        ->placeholder($locale === LocaleEnum::Es ? 'El Arte del Cuero Artesanal' : 'The Art of Handcrafted Leather')
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

                    Textarea::make("excerpt.{$locale->value}")
                        ->label(__('blog.posts.fields.excerpt')." ({$locale->label()})")
                        ->helperText(__('blog.posts.fields.excerpt_helper'))
                        ->placeholder($locale === LocaleEnum::Es ? 'Breve introducción al artículo...' : 'Brief article introduction...')
                        ->nullable()
                        ->rows(3),

                    RichEditor::make("content.{$locale->value}")
                        ->label(__('blog.posts.fields.content')." ({$locale->label()})")
                        ->required($isDefault)
                        ->nullable(! $isDefault)
                        ->columnSpanFull(),
                ]);
        })->values()->all();

        $seoTabs = collect(LocaleEnum::cases())->map(function (LocaleEnum $locale): Tab {
            return Tab::make($locale->label())
                ->schema([
                    TextInput::make("meta_title.{$locale->value}")
                        ->label(__('blog.posts.fields.meta_title')." ({$locale->label()})")
                        ->helperText(__('blog.posts.fields.meta_title_helper'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('blog.posts.fields.meta_title_tooltip'))
                        ->nullable()
                        ->maxLength(255),

                    Textarea::make("meta_description.{$locale->value}")
                        ->label(__('blog.posts.fields.meta_description')." ({$locale->label()})")
                        ->helperText(__('blog.posts.fields.meta_description_helper'))
                        ->hintIcon('heroicon-m-information-circle', tooltip: __('blog.posts.fields.meta_description_tooltip'))
                        ->nullable()
                        ->rows(2),
                ]);
        })->values()->all();

        return $schema
            ->components([
                Section::make(__('blog.posts.sections.content'))
                    ->description(__('blog.posts.sections.content_description'))
                    ->schema([
                        Tabs::make('Translations')
                            ->tabs($translationTabs)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(__('blog.posts.sections.settings'))
                    ->description(__('blog.posts.sections.settings_description'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('slug')
                                ->label(__('blog.posts.fields.slug'))
                                ->helperText(__('blog.posts.fields.slug_helper'))
                                ->hintIcon('heroicon-m-information-circle', tooltip: __('blog.posts.fields.slug_tooltip'))
                                ->required()
                                ->maxLength(255)
                                ->unique(table: Post::class, column: 'slug', ignoreRecord: true)
                                ->columnSpan(1),

                            Select::make('post_category_id')
                                ->label(__('blog.posts.fields.category'))
                                ->options(function () {
                                    return PostCategory::query()
                                        ->ordered()
                                        ->get()
                                        ->mapWithKeys(fn (PostCategory $cat) => [$cat->id => $cat->getLocalizedName()]);
                                })
                                ->searchable()
                                ->nullable()
                                ->columnSpan(1),

                            Select::make('status')
                                ->label(__('blog.posts.fields.status'))
                                ->options(PostStatusEnum::class)
                                ->default(PostStatusEnum::Draft)
                                ->required()
                                ->columnSpan(1),

                            DateTimePicker::make('published_at')
                                ->label(__('blog.posts.fields.published_at'))
                                ->helperText(__('blog.posts.fields.published_at_helper'))
                                ->nullable()
                                ->columnSpan(1),

                            FileUpload::make('cover_image_path')
                                ->label(__('blog.posts.fields.cover_image'))
                                ->helperText(__('blog.posts.fields.cover_image_helper'))
                                ->image()
                                ->disk('public')
                                ->directory('blog')
                                ->nullable()
                                ->columnSpanFull(),
                        ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('blog.posts.sections.seo'))
                    ->description(__('blog.posts.sections.seo_description'))
                    ->collapsed()
                    ->schema([
                        Tabs::make('SEO Translations')
                            ->tabs($seoTabs)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
