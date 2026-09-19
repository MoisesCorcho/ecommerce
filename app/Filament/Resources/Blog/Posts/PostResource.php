<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\Posts;

use App\Filament\Resources\Blog\Posts\Pages\CreatePost;
use App\Filament\Resources\Blog\Posts\Pages\EditPost;
use App\Filament\Resources\Blog\Posts\Pages\ListPosts;
use App\Filament\Resources\Blog\Posts\Schemas\PostForm;
use App\Filament\Resources\Blog\Posts\Schemas\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 10;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('navigation.groups.content');
    }

    public static function getModelLabel(): string
    {
        return __('blog.posts.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('blog.posts.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
