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
        return 'Categoría actualizada';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Eliminar')
                ->requiresConfirmation()
                ->modalHeading('Eliminar categoría')
                ->modalDescription('Los productos asociados quedarán sin categoría.')
                ->modalSubmitActionLabel('Sí, eliminar')
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
