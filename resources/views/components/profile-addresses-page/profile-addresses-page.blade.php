<x-partials.account-shell active="addresses">
    <div class="w-full max-w-4xl space-y-8">
        <div>
            <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('account.addresses.title') }}
            </h1>
            <p class="mt-2 text-sm text-intense-cocoa/70">
                {{ __('account.addresses.subtitle') }}
            </p>
        </div>

        @if ($statusMessage)
            <p class="border border-intense-cocoa/20 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                {{ $statusMessage }}
            </p>
        @endif

        @if ($showForm)
            <x-section-card.section-card tag="section">
                <form wire:submit="save" class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'label',
                        'label' => 'account.addresses.fields.label',
                        'wireModel' => 'wire:model="label"',
                        'colSpan' => 'sm:col-span-2',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'fullName',
                        'label' => 'account.addresses.fields.full_name',
                        'wireModel' => 'wire:model="fullName"',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'phone',
                        'label' => 'account.addresses.fields.phone',
                        'wireModel' => 'wire:model="phone"',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'addressLine1',
                        'label' => 'account.addresses.fields.address_line_1',
                        'wireModel' => 'wire:model="addressLine1"',
                        'colSpan' => 'sm:col-span-2',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'addressLine2',
                        'label' => 'account.addresses.fields.address_line_2',
                        'wireModel' => 'wire:model="addressLine2"',
                        'colSpan' => 'sm:col-span-2',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'city',
                        'label' => 'account.addresses.fields.city',
                        'wireModel' => 'wire:model="city"',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'state',
                        'label' => 'account.addresses.fields.state',
                        'wireModel' => 'wire:model="state"',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'country',
                        'label' => 'account.addresses.fields.country',
                        'wireModel' => 'wire:model="country"',
                        'inputAttributes' => 'maxlength="2"',
                    ])

                    @include('components.checkout-page.partials.text-field', [
                        'field' => 'postalCode',
                        'label' => 'account.addresses.fields.postal_code',
                        'wireModel' => 'wire:model="postalCode"',
                    ])

                    <x-checkbox id="is_default" wire:model="isDefault" wrapper-class="sm:col-span-2">
                        {{ __('account.addresses.fields.is_default') }}
                    </x-checkbox>

                    <div class="flex gap-3 sm:col-span-2">
                        <x-primary-button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="flex-1 text-label-caps"
                        >
                            {{ __('account.addresses.save') }}
                        </x-primary-button>
                        <x-secondary-button
                            type="button"
                            wire:click="cancelEdit"
                            class="flex-1 text-label-caps"
                        >
                            {{ __('account.addresses.cancel') }}
                        </x-secondary-button>
                    </div>
                </form>
            </x-section-card.section-card>
        @endif

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            @forelse ($addresses as $address)
                <x-section-card.section-card wire:key="address-{{ $address->id }}" data-address-card="{{ $address->id }}">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="mt-0.5 h-6 w-6 shrink-0 text-intense-cocoa/60" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>

                        <div class="min-w-0 flex-1">
                            @if ($address->is_default)
                                <span class="mb-2 inline-flex items-center border border-intense-cocoa px-2 py-1 text-xs font-semibold uppercase tracking-widest text-intense-cocoa">
                                    {{ __('account.addresses.default_badge') }}
                                </span>
                            @endif
                            <p class="font-semibold text-intense-cocoa">{{ $address->label ?: $address->full_name }}</p>
                            <p class="text-sm text-intense-cocoa/80">{{ $address->full_name }} · {{ $address->phone }}</p>
                            <p class="text-sm text-intense-cocoa/80">
                                {{ $address->address_line_1 }}@if ($address->address_line_2), {{ $address->address_line_2 }}@endif
                            </p>
                            <p class="text-sm text-intense-cocoa/80">
                                {{ $address->city }}, {{ $address->state }}, {{ $address->country }} {{ $address->postal_code }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm">
                        <button type="button" wire:click="edit({{ $address->id }})" class="text-intense-cocoa underline underline-offset-2 hover:text-soft-gold">
                            {{ __('account.addresses.edit') }}
                        </button>
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="text-intense-cocoa underline underline-offset-2 hover:text-soft-gold">
                                {{ __('account.addresses.make_default') }}
                            </button>
                        @endunless
                        <button
                            type="button"
                            wire:click="delete({{ $address->id }})"
                            wire:confirm="{{ __('account.addresses.confirm_delete') }}"
                            class="text-error underline underline-offset-2 hover:text-error/70"
                        >
                            {{ __('account.addresses.delete') }}
                        </button>
                    </div>
                </x-section-card.section-card>
            @empty
            @endforelse

            @unless ($showForm)
                <button
                    type="button"
                    wire:click="createNew"
                    class="flex min-h-[180px] flex-col items-center justify-center gap-2 border-2 border-dashed border-intense-cocoa/40 p-6 text-sm font-semibold text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa hover:bg-soft-sand/40"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-8 w-8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    {{ __('account.addresses.add_new') }}
                </button>
            @endunless
        </div>
    </div>
</x-partials.account-shell>
