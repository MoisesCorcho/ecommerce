<?php

use App\Support\Auth\RegisterAttemptRateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    public string $name = '';

    public string $lastName = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public bool $terms = false;

    public ?string $errorMessage = null;

    public function render()
    {
        return $this->view();
    }

    public function register(CreatesNewUsers $creator, RegisterAttemptRateLimiter $limiter): mixed
    {
        $this->errorMessage = null;

        if (! $limiter->attempt(request()->ip())) {
            $this->errorMessage = __('auth.throttle', ['seconds' => RegisterAttemptRateLimiter::DECAY_SECONDS]);

            return null;
        }

        $this->validate($this->rules());

        $user = $creator->create([
            'name' => $this->name,
            'last_name' => $this->lastName !== '' ? $this->lastName : null,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'terms' => $this->terms,
        ]);

        Auth::login($user);

        return $this->redirect(route('home'), navigate: false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }
};
