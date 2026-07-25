<div class="py-12 lg:py-16">
    <div class="mx-auto w-full max-w-2xl space-y-8 px-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                    {{ __('account.addresses.title') }}
                </h1>
                <p class="mt-2 text-sm text-intense-cocoa/70">
                    {{ __('account.addresses.subtitle') }}
                </p>
            </div>

            @unless ($showForm)
                <button
                    type="button"
                    wire:click="createNew"
                    class="h-11 bg-intense-cocoa px-6 text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa"
                >
                    {{ __('account.addresses.add_new') }}
                </button>
            @endunless
        </div>

        @if ($statusMessage)
            <p class="border border-intense-cocoa/20 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                {{ $statusMessage }}
            </p>
        @endif

        @if ($showForm)
            <section class="bg-soft-sand p-8 shadow-ambient">
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

                    <label class="flex items-center gap-2 text-sm text-intense-cocoa/80 sm:col-span-2">
                        <input type="checkbox" wire:model="isDefault" class="border-intense-cocoa/40">
                        {{ __('account.addresses.fields.is_default') }}
                    </label>

                    <div class="flex gap-3 sm:col-span-2">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="h-12 flex-1 bg-intense-cocoa text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:opacity-70"
                        >
                            {{ __('account.addresses.save') }}
                        </button>
                        <button
                            type="button"
                            wire:click="cancelEdit"
                            class="h-12 flex-1 border border-intense-cocoa/40 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa"
                        >
                            {{ __('account.addresses.cancel') }}
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <div class="space-y-4">
            @forelse ($addresses as $address)
                <div wire:key="address-{{ $address->id }}" class="bg-soft-sand p-6 shadow-ambient">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            @if ($address->is_default)
                                <span class="mb-2 inline-block bg-intense-cocoa px-2 py-1 text-xs font-semibold uppercase tracking-widest text-silk-cream">
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

                        <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
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
                    </div>
                </div>
            @empty
                <p class="text-sm text-intense-cocoa/70">{{ __('account.addresses.empty') }}</p>
            @endforelse
        </div>
    </div>
</div>
