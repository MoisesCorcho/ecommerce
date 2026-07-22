<?php

declare(strict_types=1);

namespace App\Actions\Addresses;

use App\Models\Address;

class DeleteAddressAction
{
    public function __invoke(Address $address): void
    {
        $address->delete();
    }
}
