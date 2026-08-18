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
                    <div class="sm:col-span-2">
                        <x-form-input
                            id="label"
                            wire:model="label"
                            label="{{ __('account.addresses.fields.label') }}"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="fullName"
                            wire:model="fullName"
                            label="{{ __('account.addresses.fields.full_name') }}"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="phone"
                            wire:model="phone"
                            label="{{ __('account.addresses.fields.phone') }}"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <x-form-input
                            id="addressLine1"
                            wire:model="addressLine1"
                            label="{{ __('account.addresses.fields.address_line_1') }}"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <x-form-input
                            id="addressLine2"
                            wire:model="addressLine2"
                            label="{{ __('account.addresses.fields.address_line_2') }}"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="city"
                            wire:model="city"
                            label="{{ __('account.addresses.fields.city') }}"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="state"
                            wire:model="state"
                            label="{{ __('account.addresses.fields.state') }}"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="country"
                            wire:model="country"
                            label="{{ __('account.addresses.fields.country') }}"
                            maxlength="2"
                        />
                    </div>

                    <div>
                        <x-form-input
                            id="postalCode"
                            wire:model="postalCode"
                            label="{{ __('account.addresses.fields.postal_code') }}"
                        />
                    </div>

                    <x-checkbox id="is_default" wire:model="isDefault" wire:key="checkbox-default-{{ $editingId ?? 'new' }}" wrapper-class="sm:col-span-2">
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
                    <div class="flex flex-col justify-between space-y-5">
                        {{-- 1. Header: Label Badge + Default Indicator or Make Default Action --}}
                        <div class="flex items-center justify-between gap-2 border-b border-intense-cocoa/30 pb-3">
                            <span class="inline-flex h-6 items-center border border-intense-cocoa bg-intense-cocoa px-2.5 text-[10px] font-semibold uppercase tracking-widest text-silk-cream">
                                {{ $address->label ? strtoupper($address->label) : __('account.addresses.title') }}
                            </span>
                            @if ($address->is_default)
                                <span class="inline-flex h-6 items-center gap-1 border border-soft-gold/60 bg-silk-cream px-2.5 text-xs font-semibold text-intense-cocoa">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3 text-soft-gold" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.445a1 1 0 0 0-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 0 0-.363-1.118L2.63 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.958Z" />
                                    </svg>
                                    {{ __('account.addresses.default_badge') }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:click="makeDefault({{ $address->id }})"
                                    class="inline-flex h-6 cursor-pointer items-center border border-intense-cocoa/50 bg-transparent px-2.5 text-xs font-semibold text-intense-cocoa transition-all duration-200 hover:border-soft-gold hover:text-soft-gold"
                                >
                                    {{ __('account.addresses.make_default') }}
                                </button>
                            @endif
                        </div>

                        {{-- 2. Recipient Name & Highly Legible Address Details --}}
                        <div class="space-y-2.5">
                            <h3 class="font-[family-name:var(--font-chillax)] text-lg font-semibold text-intense-cocoa">
                                {{ $address->full_name }}
                            </h3>

                            <div class="space-y-1 text-sm text-intense-cocoa">
                                <p class="font-medium leading-relaxed">
                                    {{ $address->address_line_1 }}
                                    @if ($address->address_line_2)
                                        <span class="text-intense-cocoa/90">, {{ $address->address_line_2 }}</span>
                                    @endif
                                </p>
                                <p class="text-xs font-medium text-intense-cocoa/90">
                                    {{ $address->city }}, {{ $address->state }} {{ $address->postal_code ? '('.$address->postal_code.')' : '' }} · {{ $address->country }}
                                </p>
                            </div>

                            @if ($address->phone)
                                <div class="flex items-center gap-2 pt-1 text-xs font-medium text-intense-cocoa/90">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0 text-intense-cocoa/70" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.148a1.5 1.5 0 0 1 1.465 1.175l.716 3.223a1.5 1.5 0 0 1-.452 1.418l-1.034 1.034a11.97 11.97 0 0 0 5.46 5.46l1.034-1.034a1.5 1.5 0 0 1 1.418-.452l3.223.716A1.5 1.5 0 0 1 18 15.352V16.5a1.5 1.5 0 0 1-1.5 1.5H15c-7.456 0-13.5-6.044-13.5-13.5V3.5Z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $address->phone }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- 3. Symmetrical Action Buttons Footer --}}
                        <div class="border-t border-intense-cocoa/30 pt-4">
                            @if ($confirmingDeleteId === $address->id)
                                <div class="space-y-3 border border-error/20 bg-error/5 p-3 text-center">
                                    <p class="text-xs font-semibold text-intense-cocoa">{{ __('account.addresses.confirm_delete') }}</p>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button
                                            type="button"
                                            wire:click="delete({{ $address->id }})"
                                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 border border-error/50 text-sm font-semibold text-error transition-all duration-200 hover:border-error hover:bg-error hover:text-silk-cream"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Zm3.34 0a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Z" clip-rule="evenodd" />
                                            </svg>
                                            {{ __('account.addresses.delete') }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="cancelDeleteConfirmation"
                                            class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 border border-intense-cocoa/50 text-sm font-semibold text-intense-cocoa transition-all duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
                                        >
                                            {{ __('account.addresses.cancel') }}
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        wire:click="edit({{ $address->id }})"
                                        class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 border border-intense-cocoa/50 text-sm font-semibold text-intense-cocoa transition-all duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                            <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                        </svg>
                                        {{ __('account.addresses.edit') }}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $address->id }})"
                                        class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 border border-error/50 text-sm font-semibold text-error transition-all duration-200 hover:border-error hover:bg-error hover:text-silk-cream"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Zm3.34 0a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('account.addresses.delete') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-section-card.section-card>
            @empty
            @endforelse

            @unless ($showForm || $addresses->count() >= 4)
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
