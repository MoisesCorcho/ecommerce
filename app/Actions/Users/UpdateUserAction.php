<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\Users\UpsertUserDTO;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UpdateUserAction
{
    /**
     * @throws ValidationException
     */
    public function __invoke(User $user, UpsertUserDTO $dto): User
    {
        $this->assertRequired($dto);
        $this->assertEmailUnique($dto->email, (int) $user->getKey());

        $user->name = $dto->name;
        $user->email = $dto->email;
        $user->phone = $dto->phone;

        if ($dto->password !== null && $dto->password !== '') {
            $user->password = $dto->password;
        }

        $user->save();

        return $user->refresh();
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

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * @throws ValidationException
     */
    private function assertEmailUnique(string $email, int $ignoreUserId): void
    {
        $exists = User::withTrashed()
            ->where('email', $email)
            ->whereKeyNot($ignoreUserId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'email' => __('users.validation.email_unique'),
            ]);
        }
    }
}
