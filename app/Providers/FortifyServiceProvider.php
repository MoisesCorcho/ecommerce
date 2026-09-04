<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\ResetPasswordAction;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

/**
 * Fortify is used only for its action contracts and password rules; its
 * own routes and views are disabled. Livewire full-page components call
 * these contracts directly.
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Must run in register(): the package's own provider registers its
        // routes in boot(), and every provider's register() runs before any boot().
        Fortify::ignoreRoutes();
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(RegisterUserAction::class);
        Fortify::resetUserPasswordsUsing(ResetPasswordAction::class);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject(__('auth.emails.verify_email.subject'))
                ->markdown('mail.auth.verify-email', [
                    'user' => $notifiable,
                    'url' => $url,
                    'logoUrl' => asset('images/logos/leen-brown.png'),
                ]);
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject(__('auth.emails.reset_password.subject'))
                ->markdown('mail.auth.reset-password', [
                    'user' => $notifiable,
                    'url' => $url,
                    'count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                    'logoUrl' => asset('images/logos/leen-brown.png'),
                ]);
        });
    }
}
