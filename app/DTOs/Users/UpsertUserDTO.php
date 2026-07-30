<?php

declare(strict_types=1);

namespace App\DTOs\Users;

readonly class UpsertUserDTO
{
    public function __construct(
        public string $name,
        public ?string $lastName,
        public string $email,
        public ?string $phone = null,
        public ?string $password = null,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     last_name?: string|null,
     *     email: string,
     *     phone?: string|null,
     *     password?: string|null
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        $lastName = $data['last_name'] ?? null;
        $lastName = is_string($lastName) && trim($lastName) !== '' ? trim($lastName) : null;

        $phone = $data['phone'] ?? null;
        $phone = is_string($phone) && trim($phone) !== '' ? trim($phone) : null;

        $password = $data['password'] ?? null;
        $password = is_string($password) && $password !== '' ? $password : null;

        return new self(
            name: trim((string) $data['name']),
            lastName: $lastName,
            email: strtolower(trim((string) $data['email'])),
            phone: $phone,
            password: $password,
        );
    }
}
