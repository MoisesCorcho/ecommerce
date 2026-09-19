<?php

declare(strict_types=1);

namespace App\DTOs\Contact;

readonly class SubmitContactFormDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $subject,
        public string $message,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public ?int $userId = null,
    ) {}
}
