<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Actions\Contact\SubmitContactFormAction;
use App\DTOs\Contact\SubmitContactFormDTO;
use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Mail\Contact\ContactFormSubmittedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class SubmitContactFormActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_persists_contact_submission_and_sends_mail(): void
    {
        config(['ecommerce.contact.inbox' => 'admin@leenhandbags.com']);
        Mail::fake();

        $dto = new SubmitContactFormDTO(
            name: 'Jane Doe',
            email: 'jane@example.com',
            subject: 'Custom Order',
            message: 'I want a honey bag with custom leather.',
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0 TestBrowser',
            userId: null,
        );

        $action = app(SubmitContactFormAction::class);
        $submission = $action($dto);

        $this->assertDatabaseHas('contact_submissions', [
            'id' => $submission->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Custom Order',
            'status' => ContactSubmissionStatusEnum::New->value,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 TestBrowser',
            'user_id' => null,
        ]);

        Mail::assertSent(ContactFormSubmittedMail::class, function (ContactFormSubmittedMail $mail) {
            return $mail->hasTo('admin@leenhandbags.com')
                && $mail->hasReplyTo('jane@example.com');
        });
    }

    public function test_links_authenticated_user_id_when_provided(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $dto = new SubmitContactFormDTO(
            name: $user->name,
            email: $user->email,
            subject: 'Account Question',
            message: 'Help with my account.',
            ipAddress: '127.0.0.1',
            userAgent: 'TestBrowser',
            userId: $user->id,
        );

        $action = app(SubmitContactFormAction::class);
        $submission = $action($dto);

        $this->assertSame($user->id, $submission->user_id);
        $this->assertTrue($submission->user->is($user));
    }

    public function test_persists_in_database_even_if_mail_transport_fails(): void
    {
        config(['ecommerce.contact.inbox' => 'admin@leenhandbags.com']);
        Log::shouldReceive('error')->once();

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new TransportException('Mail server timeout'));

        $dto = new SubmitContactFormDTO(
            name: 'Jane Doe',
            email: 'jane@example.com',
            subject: 'Critical Inquiry',
            message: 'Do not lose this message.',
            ipAddress: '10.0.0.1',
            userAgent: 'TestBrowser',
            userId: null,
        );

        $action = app(SubmitContactFormAction::class);
        $submission = $action($dto);

        // Resilience check: DB record MUST exist even if mail failed
        $this->assertDatabaseHas('contact_submissions', [
            'id' => $submission->id,
            'email' => 'jane@example.com',
            'subject' => 'Critical Inquiry',
            'status' => 'new',
        ]);
    }
}
