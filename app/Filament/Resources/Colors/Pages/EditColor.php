<?php

declare(strict_types=1);

namespace App\Filament\Resources\Colors\Pages;

use App\Filament\Resources\Colors\ColorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditColor extends EditRecord
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('colors.actions.delete')),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('colors.notifications.updated');
    }
}
