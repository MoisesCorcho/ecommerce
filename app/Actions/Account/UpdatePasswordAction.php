<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\Actions\Auth\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePasswordAction
{
    use PasswordValidationRules;

    /**
     * @throws ValidationException
     */
    public function __invoke(User $user, string $currentPassword, string $newPassword, string $newPasswordConfirmation): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('account.errors.current_password_incorrect')],
            ]);
        }

        Validator::make(
            [
                'new_password' => $newPassword,
                'new_password_confirmation' => $newPasswordConfirmation,
            ],
            [
                'new_password' => $this->passwordRules(),
            ],
        )->validate();

        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();
    }
}
