<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\PostCategories;

use App\Filament\Resources\Blog\PostCategories\Pages\CreatePostCategory;
use App\Filament\Resources\Blog\PostCategories\Pages\EditPostCategory;
use App\Filament\Resources\Blog\PostCategories\Pages\ListPostCategories;
use App\Filament\Resources\Blog\PostCategories\Schemas\PostCategoriesTable;
use App\Filament\Resources\Blog\PostCategories\Schemas\PostCategoryForm;
use App\Models\PostCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostCategoryResource extends Resource
{
    protected static ?string $model = PostCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 20;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.content');
    }

    public static function getModelLabel(): string
    {
        return __('blog.categories.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('blog.categories.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return PostCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPostCategories::route('/'),
            'create' => CreatePostCategory::route('/create'),
            'edit' => EditPostCategory::route('/{record}/edit'),
        ];
    }
}
