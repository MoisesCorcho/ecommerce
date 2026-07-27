<?php

declare(strict_types=1);

namespace App\Mail\Contact;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * First Mailable in this app. Intentionally NOT ShouldQueue: this feature has
 * no DB fallback for the submitted message, so a queued transport failure
 * would happen invisibly and the storefront would show a false success. Sync
 * send is the only way the "send failed" banner stays truthful.
 */
final class ContactFormSubmittedMail extends Mailable
{
    public function __construct(
        public readonly string $senderName,
        public readonly string $senderEmail,
        public readonly string $subjectLine,
        public readonly string $body,
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
        return new Content(view: 'mail.contact.submitted');
    }
}
