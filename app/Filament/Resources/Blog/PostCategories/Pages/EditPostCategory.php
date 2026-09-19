<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\PostCategories\Pages;

use App\Filament\Resources\Blog\PostCategories\PostCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPostCategory extends EditRecord
{
    protected static string $resource = PostCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
