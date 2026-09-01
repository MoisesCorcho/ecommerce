<?php

declare(strict_types=1);

namespace App\Filament\Resources\Blog\Posts\Pages;

use App\Filament\Resources\Blog\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['author_id']) && auth()->check()) {
            $data['author_id'] = auth()->id();
        }

        return $data;
    }
}
