<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\Users\UpsertUserDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateUserAction
{
    /**
     * @throws ValidationException
     */
    public function __invoke(UpsertUserDTO $dto): User
    {
        $this->assertRequired($dto);
        $this->assertEmailUnique($dto->email);

        return User::query()->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'password' => $dto->password,
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function assertRequired(UpsertUserDTO $dto): void
    {
        $errors = [];

        if ($dto->name === '') {
            $errors['name'] = __('users.validation.name_required');
        }

        if ($dto->email === '') {
            $errors['email'] = __('users.validation.email_required');
        } elseif (! filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = __('users.validation.email_invalid');
        }

        if ($dto->password === null || $dto->password === '') {
            $errors['password'] = __('users.validation.password_required_on_create');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertEmailUnique(string $email): void
    {
        $exists = User::withTrashed()
            ->where('email', $email)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => __('users.validation.email_unique'),
            ]);
        }
    }
}
