<?php

use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $errorMessage = null;

    public function render()
    {
        return $this->view()->title('Leen Handbags | '.__('auth.reset_password.title'));
    }

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
    }

    public function resetPassword(ResetsUserPasswords $resetter): mixed
    {
        $this->errorMessage = null;

        $this->validate($this->rules());

        $status = Password::reset(
            $this->only(['email', 'password', 'password_confirmation', 'token']),
            function ($user, string $password) use ($resetter): void {
                $resetter->reset($user, ['password' => $password, 'password_confirmation' => $password]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->errorMessage = __($status);

            return null;
        }

        return $this->redirect(route('login'), navigate: false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed'],
        ];
    }
};
