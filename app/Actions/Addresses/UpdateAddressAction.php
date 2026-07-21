<?php

declare(strict_types=1);

namespace App\Actions\Addresses;

use App\DTOs\Addresses\UpsertAddressDTO;
use App\Models\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateAddressAction
{
    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(Address $address, UpsertAddressDTO $dto): Address
    {
        $this->assertValid($dto);

        return DB::transaction(function () use ($address, $dto): Address {
            // Reload so we don't miss DB-side default clears from other addresses.
            $address = Address::query()->lockForUpdate()->findOrFail($address->getKey());

            if ($dto->isDefault) {
                Address::query()
                    ->where('user_id', $dto->userId)
                    ->whereKeyNot($address->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            $address->fill([
                'user_id' => $dto->userId,
                'label' => $dto->label,
                'full_name' => $dto->fullName,
                'phone' => $dto->phone,
                'address_line_1' => $dto->addressLine1,
                'address_line_2' => $dto->addressLine2,
                'city' => $dto->city,
                'state' => $dto->state,
                'country' => $dto->country,
                'postal_code' => $dto->postalCode,
            ]);
            // Assign separately so a stale in-memory true→true after external clear still persists.
            $address->is_default = $dto->isDefault;
            $address->save();

            return $address->refresh();
        });
    }

    /**
     * @throws ValidationException
     */
    private function assertValid(UpsertAddressDTO $dto): void
    {
        $errors = [];

        if ($dto->fullName === '') {
            $errors['full_name'] = 'El nombre completo es obligatorio.';
        }

        if ($dto->phone === '') {
            $errors['phone'] = 'El teléfono es obligatorio.';
        }

        if ($dto->addressLine1 === '') {
            $errors['address_line_1'] = 'La línea 1 de dirección es obligatoria.';
        }

        if ($dto->city === '') {
            $errors['city'] = 'La ciudad es obligatoria.';
        }

        if ($dto->state === '') {
            $errors['state'] = 'El estado o departamento es obligatorio.';
        }

        if ($dto->country === '' || ! preg_match('/^[A-Z]{2}$/', $dto->country)) {
            $errors['country'] = 'El país debe ser un código ISO de 2 letras.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
