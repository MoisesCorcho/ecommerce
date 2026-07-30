<x-section-card.section-card class="w-full md:p-10">
    <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
        {{ __('auth.register.title') }}
    </h1>
    <p class="mt-2 text-sm text-intense-cocoa/70">
        {{ __('auth.register.subtitle') }}
    </p>

    @if ($errorMessage)
        <x-alert type="error" class="mt-6">
            {{ $errorMessage }}
        </x-alert>
    @endif

    <form wire:submit="register" class="mt-6 space-y-5">
        {{-- Name --}}
        <x-form-input
            id="name"
            type="text"
            wire:model="name"
            autocomplete="name"
            label="{{ __('auth.fields.name') }}"
            placeholder="{{ __('auth.register.placeholders.name') }}"
        >
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </x-slot:icon>
        </x-form-input>

        {{-- Last name --}}
        <x-form-input
            id="lastName"
            type="text"
            wire:model="lastName"
            autocomplete="family-name"
            label="{{ __('auth.fields.last_name') }}"
            placeholder="{{ __('auth.register.placeholders.last_name') }}"
        >
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </x-slot:icon>
        </x-form-input>

        {{-- Email --}}
        <x-form-input
            id="email"
            type="email"
            wire:model="email"
            autocomplete="email"
            label="{{ __('auth.fields.email') }}"
            placeholder="{{ __('auth.register.placeholders.email') }}"
        >
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </x-slot:icon>
        </x-form-input>

        {{-- Password --}}
        <div x-data="{ value: '' }">
            <x-password-input
                id="password"
                wire:model="password"
                label="{{ __('auth.fields.password') }}"
                placeholder="{{ __('auth.register.placeholders.password') }}"
                autocomplete="new-password"
                x-on:input="value = $event.target.value"
            >
                {{-- Strength meter (decorative, non-authoritative) --}}
                <div class="mt-2 flex gap-1" x-show="value.length > 0" x-cloak aria-hidden="true">
                    <template x-for="i in 3" :key="i">
                        <span
                            class="h-1 flex-1 transition-colors"
                            :class="i > [value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length
                                ? 'bg-intense-cocoa/15'
                                : ([value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length === 1
                                    ? 'bg-error'
                                    : ([value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length === 2 ? 'bg-soft-gold' : 'bg-success'))"
                        ></span>
                    </template>
                </div>
                <p class="mt-1 text-sm" role="status" aria-live="polite" x-show="value.length > 0" x-cloak>
                    <template x-if="[value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length &lt;= 1">
                        <span class="text-intense-cocoa">{{ __('auth.register.strength.weak') }}</span>
                    </template>
                    <template x-if="[value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length === 2">
                        <span class="text-intense-cocoa">{{ __('auth.register.strength.medium') }}</span>
                    </template>
                    <template x-if="[value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value)].filter(Boolean).length === 3">
                        <span class="text-intense-cocoa">{{ __('auth.register.strength.strong') }}</span>
                    </template>
                </p>
            </x-password-input>
        </div>

        {{-- Password confirmation --}}
        <x-password-input
            id="password_confirmation"
            wire:model="password_confirmation"
            label="{{ __('auth.fields.password_confirmation') }}"
            placeholder="{{ __('auth.register.placeholders.password_confirmation') }}"
            autocomplete="new-password"
        />

        {{-- Terms and conditions --}}
        <div>
            <x-checkbox id="terms" wire:model="terms" error="terms">
                {{ __('auth.register.terms.prefix') }}
                @if (Route::has('terms'))
                    <a href="{{ route('terms') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">{{ __('auth.register.terms.terms_link') }}</a>
                @else
                    <span class="font-medium text-intense-cocoa">{{ __('auth.register.terms.terms_link') }}</span>
                @endif
                {{ __('auth.register.terms.connector') }}
                @if (Route::has('privacy'))
                    <a href="{{ route('privacy') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">{{ __('auth.register.terms.privacy_link') }}</a>
                @else
                    <span class="font-medium text-intense-cocoa">{{ __('auth.register.terms.privacy_link') }}</span>
                @endif
            </x-checkbox>
        </div>

        <x-primary-button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="register"
            class="mt-2 w-full gap-2 text-label-caps"
        >
            <svg wire:loading wire:target="register" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="register">{{ __('auth.register.submit') }}</span>
            <span wire:loading wire:target="register">{{ __('auth.register.submitting') }}</span>
        </x-primary-button>

        <p class="mt-4 text-center text-sm text-intense-cocoa/70">
            {{ __('auth.register.have_account') }}
            <a href="{{ route('login') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">
                {{ __('auth.register.login_link') }}
            </a>
        </p>
    </form>
</x-section-card.section-card>
