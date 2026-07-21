<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\DTOs\Users\UpsertUserDTO;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getSavedNotificationTitle(): ?string
    {
        return __('users.notifications.updated');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('users.actions.delete'))
                ->requiresConfirmation()
                ->modalHeading(__('users.modals.delete_heading'))
                ->modalDescription(__('users.modals.delete_description'))
                ->modalSubmitActionLabel(__('users.actions.confirm_delete'))
                ->using(function (User $record): void {
                    app(DeleteUserAction::class)($record);
                }),
        ];
    }

    /**
     * Never show the stored password hash in the form.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['password'] = null;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        return app(UpdateUserAction::class)($record, UpsertUserDTO::fromArray($data));
    }
}
