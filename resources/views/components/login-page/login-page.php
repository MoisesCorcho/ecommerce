<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.storefront'), Title('Leen Handbags | Iniciar sesión')] class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public ?string $errorMessage = null;

    public function login(LoginRateLimiter $limiter, Request $request): mixed
    {
        $this->errorMessage = null;

        $this->validate($this->rules());

        $request->merge([Fortify::username() => $this->email]);

        if ($limiter->tooManyAttempts($request)) {
            $this->errorMessage = __('auth.throttle', ['seconds' => $limiter->availableIn($request)]);

            return null;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $limiter->increment($request);
            $this->errorMessage = __('auth.failed');

            return null;
        }

        $limiter->clear($request);
        Session::regenerate();

        return $this->redirect(route('home'), navigate: false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }
};
