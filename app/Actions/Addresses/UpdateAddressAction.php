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
            $errors['full_name'] = __('addresses.validation.full_name_required');
        }

        if ($dto->phone === '') {
            $errors['phone'] = __('addresses.validation.phone_required');
        }

        if ($dto->addressLine1 === '') {
            $errors['address_line_1'] = __('addresses.validation.address_line_1_required');
        }

        if ($dto->city === '') {
            $errors['city'] = __('addresses.validation.city_required');
        }

        if ($dto->state === '') {
            $errors['state'] = __('addresses.validation.state_required');
        }

        if ($dto->country === '' || ! preg_match('/^[A-Z]{2}$/', $dto->country)) {
            $errors['country'] = __('addresses.validation.country_invalid');
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
