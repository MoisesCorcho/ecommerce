<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Filament\Resources\ContactSubmissions\Pages\ViewContactSubmission;
use App\Mail\Contact\ContactFormSubmittedMail;
use App\Models\ContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactFormSubmittedMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_renders_markdown_with_logo_and_details(): void
    {
        $submission = ContactSubmission::factory()->create([
            'name' => 'Ana Perez',
            'email' => 'ana@example.com',
            'subject' => 'Consulta sobre bolso',
            'message' => 'Tienen envios a Medellin?',
        ]);

        $mail = new ContactFormSubmittedMail(
            senderName: $submission->name,
            senderEmail: $submission->email,
            subjectLine: $submission->subject,
            body: $submission->message,
            submission: $submission,
        );

        $mail->assertHasSubject(__('contact.mail.subject', ['subject' => 'Consulta sobre bolso']));
        $mail->assertHasReplyTo('ana@example.com');

        $html = $mail->render();

        $this->assertStringContainsString('images/logos/leen-brown.png', $html);
        $this->assertStringContainsString('Ana Perez', $html);
        $this->assertStringContainsString('ana@example.com', $html);
        $this->assertStringContainsString('Tienen envios a Medellin?', $html);

        $adminUrl = ViewContactSubmission::getUrl(['record' => $submission]);
        $this->assertStringContainsString($adminUrl, $html);
        $this->assertStringNotContainsString('laravel.com/img/notification-logo', $html);
    }
}
