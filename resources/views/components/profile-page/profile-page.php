<?php

declare(strict_types=1);

use App\Actions\Account\UpdatePasswordAction;
use App\Actions\Account\UpdateProfileAction;
use App\DTOs\Account\UpdateProfileDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public function render()
    {
        return $this->view();
    }

    public string $name = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public ?string $profileMessage = null;

    public ?string $passwordMessage = null;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->lastName = (string) ($user->last_name ?? '');
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    public function updateProfile(UpdateProfileAction $action): void
    {
        $this->profileMessage = null;

        $user = Auth::user();

        try {
            $action($user, new UpdateProfileDTO(
                userId: (int) $user->id,
                name: $this->name,
                lastName: $this->lastName !== '' ? $this->lastName : null,
                email: $this->email,
                phone: $this->phone !== '' ? $this->phone : null,
            ));
        } catch (ValidationException $e) {
            $this->addFieldErrors($e);

            return;
        }

        $this->profileMessage = __('account.profile.updated');
    }

    public function updatePassword(UpdatePasswordAction $action): void
    {
        $this->passwordMessage = null;

        try {
            $action(Auth::user(), $this->current_password, $this->new_password, $this->new_password_confirmation);
        } catch (ValidationException $e) {
            $this->addFieldErrors($e);

            return;
        }

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->passwordMessage = __('account.password.updated');
    }

    private function addFieldErrors(ValidationException $e): void
    {
        foreach ($e->errors() as $field => $messages) {
            $this->addError($field, $messages[0]);
        }
    }
};
