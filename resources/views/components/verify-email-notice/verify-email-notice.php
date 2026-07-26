<?php

use App\Support\Auth\VerifyEmailResendRateLimiter;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public bool $resent = false;

    public ?string $errorMessage = null;

    public function render()
    {
        return $this->view();
    }

    public function mount(): mixed
    {
        if (Auth::user()?->hasVerifiedEmail()) {
            return $this->redirect(route('home'), navigate: false);
        }

        return null;
    }

    public function resend(VerifyEmailResendRateLimiter $limiter): void
    {
        $this->resent = false;
        $this->errorMessage = null;

        if (! $limiter->attempt((int) Auth::id())) {
            $this->errorMessage = __('auth.throttle', ['seconds' => VerifyEmailResendRateLimiter::DECAY_SECONDS]);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();
        $this->resent = true;
    }
};
