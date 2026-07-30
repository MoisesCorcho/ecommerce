<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\DTOs\Account\UpdateProfileDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateProfileAction
{
    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(User $user, UpdateProfileDTO $dto): User
    {
        Validator::make(
            [
                'name' => $dto->name,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'phone' => $dto->phone,
            ],
            [
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($dto->userId)],
                'phone' => ['nullable', 'string', 'max:50'],
            ],
        )->validate();

        $emailChanged = strcasecmp($dto->email, $user->email) !== 0;

        return DB::transaction(function () use ($user, $dto, $emailChanged): User {
            $user->fill([
                'name' => $dto->name,
                'last_name' => $dto->lastName,
                'email' => $dto->email,
                'phone' => $dto->phone,
            ]);

            if ($emailChanged) {
                $user->markEmailAsUnverified();
            }

            $user->save();

            if ($emailChanged) {
                $user->sendEmailVerificationNotification();
            }

            return $user->refresh();
        });
    }
}
