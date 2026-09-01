<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\PostCategories\Pages;

use App\Filament\Resources\Blog\PostCategories\PostCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePostCategory extends CreateRecord
{
    protected static string $resource = PostCategoryResource::class;
}
