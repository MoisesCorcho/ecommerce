<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Pages;

use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return __('categories.notifications.updated');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('categories.actions.delete'))
                ->requiresConfirmation()
                ->modalHeading(__('categories.modals.delete_heading'))
                ->modalDescription(__('categories.modals.delete_description'))
                ->modalSubmitActionLabel(__('categories.actions.confirm_delete'))
                ->using(function (Category $record): void {
                    app(DeleteCategoryAction::class)($record);
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Category $record */
        return app(UpdateCategoryAction::class)($record, $data);
    }
}
