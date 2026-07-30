<x-partials.account-shell active="profile">
    <div class="w-full max-w-2xl space-y-10">
        <div>
            <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('account.profile.title') }}
            </h1>
            <p class="mt-2 text-sm text-intense-cocoa/70">
                {{ __('account.profile.greeting', ['name' => auth()->user()->name]) }}
            </p>
            <p class="mt-1 text-sm text-intense-cocoa/70">
                {{ __('account.profile.subtitle') }}
            </p>
        </div>

        <x-section-card.section-card tag="section">
            <h2 class="text-lg font-semibold text-intense-cocoa">{{ __('account.profile.section_title') }}</h2>

            @if ($profileMessage)
                <p class="mt-4 border border-intense-cocoa/20 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                    {{ $profileMessage }}
                </p>
            @endif

            <form wire:submit="updateProfile" class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-1">
                    <x-form-input
                        id="name"
                        wire:model="name"
                        label="{{ __('account.fields.name') }}"
                    />
                </div>

                <div class="sm:col-span-1">
                    <x-form-input
                        id="lastName"
                        wire:model="lastName"
                        label="{{ __('account.fields.last_name') }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-form-input
                        id="email"
                        type="email"
                        wire:model="email"
                        label="{{ __('account.fields.email') }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-form-input
                        id="phone"
                        wire:model="phone"
                        label="{{ __('account.fields.phone') }}"
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-primary-button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updateProfile"
                        class="text-label-caps sm:w-auto sm:px-8"
                    >
                        {{ __('account.profile.submit') }}
                    </x-primary-button>
                </div>
            </form>
        </x-section-card.section-card>

        <x-section-card.section-card tag="section" id="password">
            <h2 class="text-lg font-semibold text-intense-cocoa">{{ __('account.password.section_title') }}</h2>

            @if ($passwordMessage)
                <p class="mt-4 border border-intense-cocoa/20 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                    {{ $passwordMessage }}
                </p>
            @endif

            <form wire:submit="updatePassword" class="mt-6 grid grid-cols-1 gap-5">
                <x-password-input
                    id="current_password"
                    wire:model="current_password"
                    label="{{ __('account.password.current') }}"
                    autocomplete="current-password"
                    :show-lock-icon="false"
                />

                <x-password-input
                    id="new_password"
                    wire:model="new_password"
                    label="{{ __('account.password.new') }}"
                    autocomplete="new-password"
                    :show-lock-icon="false"
                />

                <x-password-input
                    id="new_password_confirmation"
                    wire:model="new_password_confirmation"
                    label="{{ __('account.password.confirmation') }}"
                    autocomplete="new-password"
                    :show-lock-icon="false"
                />

                <div>
                    <x-primary-button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updatePassword"
                        class="text-label-caps sm:w-auto sm:px-8"
                    >
                        {{ __('account.password.submit') }}
                    </x-primary-button>
                </div>
            </form>
        </x-section-card.section-card>
    </div>
</x-partials.account-shell>
