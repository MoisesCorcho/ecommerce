<?php

declare(strict_types=1);

use App\Actions\Addresses\CreateAddressAction;
use App\Actions\Addresses\DeleteAddressAction;
use App\Actions\Addresses\UpdateAddressAction;
use App\DTOs\Addresses\UpsertAddressDTO;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public function render()
    {
        return $this->view();
    }

    public ?int $editingId = null;

    public string $label = '';

    public string $fullName = '';

    public string $phone = '';

    public string $addressLine1 = '';

    public string $addressLine2 = '';

    public string $city = '';

    public string $state = '';

    public string $country = 'CO';

    public string $postalCode = '';

    public bool $isDefault = false;

    public ?int $confirmingDeleteId = null;

    public bool $showForm = false;

    public ?string $statusMessage = null;

    public function with(): array
    {
        return [
            'addresses' => Auth::user()->addresses()
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get(),
        ];
    }

    public function createNew(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $addressId): void
    {
        $address = Address::query()->findOrFail($addressId);

        Gate::authorize('view', $address);

        $this->editingId = $address->id;
        $this->label = (string) ($address->label ?? '');
        $this->fullName = $address->full_name;
        $this->phone = $address->phone;
        $this->addressLine1 = $address->address_line_1;
        $this->addressLine2 = (string) ($address->address_line_2 ?? '');
        $this->city = $address->city;
        $this->state = $address->state;
        $this->country = $address->country;
        $this->postalCode = (string) ($address->postal_code ?? '');
        $this->isDefault = (bool) $address->is_default;
        $this->showForm = true;
    }

    public function save(CreateAddressAction $create, UpdateAddressAction $update): void
    {
        $this->statusMessage = null;

        $dto = UpsertAddressDTO::fromArray([
            'user_id' => Auth::id(),
            'label' => $this->label,
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postalCode,
            'is_default' => $this->isDefault,
        ]);

        try {
            if ($this->editingId !== null) {
                $address = Address::query()->findOrFail($this->editingId);
                Gate::authorize('update', $address);
                $update($address, $dto);
            } else {
                $create($dto);
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->statusMessage = __('account.addresses.saved');
        $this->resetForm();
    }

    public function makeDefault(int $addressId, UpdateAddressAction $update): void
    {
        $address = Address::query()->findOrFail($addressId);
        Gate::authorize('update', $address);

        $dto = UpsertAddressDTO::fromArray([
            'user_id' => $address->user_id,
            'label' => $address->label,
            'full_name' => $address->full_name,
            'phone' => $address->phone,
            'address_line_1' => $address->address_line_1,
            'address_line_2' => $address->address_line_2,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'postal_code' => $address->postal_code,
            'is_default' => true,
        ]);

        $update($address, $dto);

        $this->statusMessage = __('account.addresses.default_updated');
    }

    public function confirmDelete(int $addressId): void
    {
        $this->confirmingDeleteId = $addressId;
    }

    public function cancelDeleteConfirmation(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $addressId, DeleteAddressAction $action): void
    {
        $address = Address::query()->findOrFail($addressId);
        Gate::authorize('delete', $address);

        $action($address);

        if ($this->editingId === $addressId) {
            $this->resetForm();
        }

        $this->confirmingDeleteId = null;
        $this->statusMessage = __('account.addresses.deleted');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'label',
            'fullName',
            'phone',
            'addressLine1',
            'addressLine2',
            'city',
            'state',
            'postalCode',
            'isDefault',
            'showForm',
        ]);
        $this->country = 'CO';
        $this->resetValidation();
    }
};
