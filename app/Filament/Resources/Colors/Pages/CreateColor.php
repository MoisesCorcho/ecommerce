<?php

declare(strict_types=1);

namespace App\Filament\Resources\Colors\Pages;

use App\Filament\Resources\Colors\ColorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateColor extends CreateRecord
{
    protected static string $resource = ColorResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('colors.pages.create_title');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('colors.notifications.created');
    }
}
