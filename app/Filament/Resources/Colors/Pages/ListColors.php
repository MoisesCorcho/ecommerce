<?php

declare(strict_types=1);

namespace App\Filament\Resources\Colors\Pages;

use App\Filament\Resources\Colors\ColorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListColors extends ListRecords
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('colors.actions.create'))
                ->icon(Heroicon::Plus),
        ];
    }
}
