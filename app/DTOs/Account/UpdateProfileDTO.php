<?php

declare(strict_types=1);

namespace App\DTOs\Account;

readonly class UpdateProfileDTO
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?string $lastName,
        public string $email,
        public ?string $phone = null,
    ) {}
}
