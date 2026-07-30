<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Actions\Categories\CreateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('categories.pages.create_title');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('categories.notifications.created');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateCategoryAction::class)($data);
    }
}
