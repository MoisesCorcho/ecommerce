<?php

declare(strict_types=1);

namespace App\Filament\Resources\PromotionalPopups\Pages;

use App\Filament\Resources\PromotionalPopups\PromotionalPopupResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePromotionalPopup extends CreateRecord
{
    protected static string $resource = PromotionalPopupResource::class;
}
