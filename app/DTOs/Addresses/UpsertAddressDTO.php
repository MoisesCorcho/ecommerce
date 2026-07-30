<?php

declare(strict_types=1);

namespace App\DTOs\Addresses;

readonly class UpsertAddressDTO
{
    public function __construct(
        public int $userId,
        public string $fullName,
        public string $phone,
        public string $addressLine1,
        public string $city,
        public string $state,
        public string $country,
        public ?string $label = null,
        public ?string $addressLine2 = null,
        public ?string $postalCode = null,
        public bool $isDefault = false,
    ) {}

    /**
     * @param  array{
     *     user_id: int|string,
     *     full_name: string,
     *     phone: string,
     *     address_line_1: string,
     *     city: string,
     *     state: string,
     *     country?: string|null,
     *     label?: string|null,
     *     address_line_2?: string|null,
     *     postal_code?: string|null,
     *     is_default?: bool|int|string|null
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        $label = $data['label'] ?? null;
        $label = is_string($label) && trim($label) !== '' ? trim($label) : null;

        $line2 = $data['address_line_2'] ?? null;
        $line2 = is_string($line2) && trim($line2) !== '' ? trim($line2) : null;

        $postal = $data['postal_code'] ?? null;
        $postal = is_string($postal) && trim($postal) !== '' ? trim($postal) : null;

        if (! array_key_exists('country', $data) || $data['country'] === null) {
            $country = 'CO';
        } else {
            $country = strtoupper(trim((string) $data['country']));
        }

        return new self(
            userId: (int) $data['user_id'],
            fullName: trim((string) $data['full_name']),
            phone: trim((string) $data['phone']),
            addressLine1: trim((string) $data['address_line_1']),
            city: trim((string) $data['city']),
            state: trim((string) $data['state']),
            country: $country,
            label: $label,
            addressLine2: $line2,
            postalCode: $postal,
            isDefault: filter_var($data['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
        );
    }
}
