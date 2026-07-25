<?php

use App\Support\Auth\PasswordResetRequestRateLimiter;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront'), Title('Leen Handbags | Restablecer contraseña')] class extends Component
{
    public string $email = '';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function sendResetLink(PasswordResetRequestRateLimiter $limiter): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        if (! $limiter->attempt(request()->ip())) {
            $this->errorMessage = __('auth.throttle', ['seconds' => PasswordResetRequestRateLimiter::DECAY_SECONDS]);

            return;
        }

        $this->validate($this->rules());

        Password::sendResetLink(['email' => $this->email]);

        // Response is always the same regardless of whether the email exists.
        $this->statusMessage = __('passwords.sent');
        $this->email = '';
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }
};
