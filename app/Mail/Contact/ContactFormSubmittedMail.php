<?php

declare(strict_types=1);

namespace App\Mail\Contact;

use App\Filament\Resources\ContactSubmissions\Pages\ViewContactSubmission;
use App\Models\ContactSubmission;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class ContactFormSubmittedMail extends Mailable
{
    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $subjectLine,
        public readonly string $body,
        public readonly ?ContactSubmission $submission = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->senderEmail,
            subject: __('contact.mail.subject', ['subject' => $this->subjectLine]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contact.submitted',
            with: [
                'adminUrl' => $this->submission !== null
                    ? ViewContactSubmission::getUrl(['record' => $this->submission])
                    : null,
                'logoUrl' => asset('images/logos/leen-brown.png'),
            ],
        );
    }
}
