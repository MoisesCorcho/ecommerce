<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups\Pages;

use App\Filament\Resources\PromotionalPopups\PromotionalPopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPromotionalPopups extends ListRecords
{
    protected static string $resource = PromotionalPopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
