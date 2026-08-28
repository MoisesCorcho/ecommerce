<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups\Pages;

use App\Filament\Resources\PromotionalPopups\PromotionalPopupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPromotionalPopup extends EditRecord
{
    protected static string $resource = PromotionalPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
