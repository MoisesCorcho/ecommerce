<?php

declare(strict_types=1);

namespace App\Actions\Contact;

use App\DTOs\Contact\SubmitContactFormDTO;
use App\Enums\Contact\ContactSubmissionStatusEnum;
use App\Mail\Contact\ContactFormSubmittedMail;
use App\Models\ContactSubmission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubmitContactFormAction
{
    public function __invoke(SubmitContactFormDTO $dto): ContactSubmission
    {
        $submission = ContactSubmission::query()->create([
            'user_id' => $dto->userId,
            'name' => $dto->name,
            'email' => $dto->email,
            'subject' => $dto->subject,
            'message' => $dto->message,
            'status' => ContactSubmissionStatusEnum::New,
            'ip_address' => $dto->ipAddress,
            'user_agent' => $dto->userAgent,
        ]);

        $inbox = config('ecommerce.contact.inbox');
        if (! empty($inbox)) {
            try {
                Mail::to($inbox)->send(new ContactFormSubmittedMail(
                    senderName: $dto->name,
                    senderEmail: $dto->email,
                    subjectLine: $dto->subject,
                    body: $dto->message,
                    submission: $submission,
                ));
            } catch (Throwable $e) {
                Log::error('Contact form submission failed to send mail.', [
                    'submission_id' => $submission->id,
                    'exception' => $e->getMessage(),
                    'sender_email' => $dto->email,
                ]);
            }
        }

        return $submission;
    }
}
