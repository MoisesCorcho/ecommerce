<x-section-card.section-card class="w-full md:p-10">
    <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
        {{ __('auth.login.title') }}
    </h1>
    <p class="mt-2 text-sm text-intense-cocoa/70">
        {{ __('auth.login.subtitle') }}
    </p>

    @if ($errorMessage)
        <x-alert type="error" class="mt-6">
            {{ $errorMessage }}
        </x-alert>
    @endif

    <form wire:submit="login" class="mt-6 space-y-5">
        {{-- Email --}}
        <x-form-input
            id="email"
            type="email"
            wire:model="email"
            autocomplete="email"
            label="{{ __('auth.fields.email') }}"
            placeholder="{{ __('auth.login.placeholders.email') }}"
        >
            <x-slot:icon>
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </x-slot:icon>
        </x-form-input>

        {{-- Password --}}
        <x-password-input
            id="password"
            wire:model="password"
            label="{{ __('auth.fields.password') }}"
            placeholder="{{ __('auth.login.placeholders.password') }}"
            autocomplete="current-password"
        />

        <x-checkbox id="remember" wire:model="remember">
            {{ __('auth.login.remember') }}
        </x-checkbox>

        <x-primary-button
            type="submit"
            wire:loading.attr="disabled"
            wire:target="login"
            class="mt-2 w-full gap-2 text-label-caps"
        >
            <svg wire:loading wire:target="login" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
            </svg>
            <span wire:loading.remove wire:target="login">{{ __('auth.login.submit') }}</span>
            <span wire:loading wire:target="login">{{ __('auth.login.submitting') }}</span>
        </x-primary-button>

        <div class="flex flex-col items-center gap-2 text-sm">
            <a href="{{ route('password.request') }}" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">
                {{ __('auth.login.forgot_password') }}
            </a>
            <p class="text-intense-cocoa/70">
                {{ __('auth.login.no_account') }}
                <a href="{{ route('register') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">
                    {{ __('auth.login.register_link') }}
                </a>
            </p>
        </div>
    </form>
</x-section-card.section-card>
