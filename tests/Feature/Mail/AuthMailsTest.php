<?php

declare(strict_types=1);

namespace Tests\Feature\Mail;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class AuthMailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_notification_renders_in_spanish_with_leen_branding(): void
    {
        App::setLocale('es');
        $user = User::factory()->create(['name' => 'Mariana']);

        $notification = new VerifyEmail;
        $mailMessage = $notification->toMail($user);

        $this->assertSame('Verifica tu dirección de correo electrónico', $mailMessage->subject);

        $rendered = (string) app(Markdown::class)->render($mailMessage->markdown, $mailMessage->data());

        $this->assertStringContainsString('Mariana', $rendered);
        $this->assertStringContainsString('leen-brown.png', $rendered);
        $this->assertStringContainsString('#FBF9F5', $rendered);
        $this->assertStringContainsString('Verificar dirección de correo', $rendered);
        $this->assertStringContainsString('Leen Handbags', $rendered);
    }

    public function test_reset_password_notification_renders_in_spanish_with_leen_branding(): void
    {
        App::setLocale('es');
        $user = User::factory()->create(['name' => 'Carlos']);

        $notification = new ResetPassword('sample-token-123');
        $mailMessage = $notification->toMail($user);

        $this->assertSame('Restablece tu contraseña', $mailMessage->subject);

        $rendered = (string) app(Markdown::class)->render($mailMessage->markdown, $mailMessage->data());

        $this->assertStringContainsString('Carlos', $rendered);
        $this->assertStringContainsString('leen-brown.png', $rendered);
        $this->assertStringContainsString('#FBF9F5', $rendered);
        $this->assertStringContainsString('Restablecer contraseña', $rendered);
        $this->assertStringContainsString('sample-token-123', $rendered);
        $this->assertStringContainsString('Leen Handbags', $rendered);
    }

    public function test_auth_notifications_support_english_locale(): void
    {
        App::setLocale('en');
        $user = User::factory()->create(['name' => 'John']);

        $verifyNotification = new VerifyEmail;
        $verifyMail = $verifyNotification->toMail($user);
        $this->assertSame('Verify your email address', $verifyMail->subject);
        $verifyRendered = (string) app(Markdown::class)->render($verifyMail->markdown, $verifyMail->data());
        $this->assertStringContainsString('Verify Email Address', $verifyRendered);

        $resetNotification = new ResetPassword('test-token');
        $resetMail = $resetNotification->toMail($user);
        $this->assertSame('Reset your password', $resetMail->subject);
        $resetRendered = (string) app(Markdown::class)->render($resetMail->markdown, $resetMail->data());
        $this->assertStringContainsString('Reset Password', $resetRendered);
    }
}
