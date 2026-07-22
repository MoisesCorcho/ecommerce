<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\CreateUserAction;
use App\DTOs\Users\UpsertUserDTO;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string|Htmlable
    {
        return __('users.pages.create_title');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('users.notifications.created');
    }

    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateUserAction::class)(UpsertUserDTO::fromArray($data));
    }
}
