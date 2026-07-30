<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-6 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.reset_password.title') }}
        </h1>

        @if ($errorMessage)
            <x-alert type="error" class="mb-4">
                {{ $errorMessage }}
            </x-alert>
        @endif

        <form wire:submit="resetPassword" class="space-y-4">
            <x-form-input
                id="email"
                type="email"
                wire:model="email"
                autocomplete="email"
                label="{{ __('auth.fields.email') }}"
            />
            <x-password-input
                id="password"
                wire:model="password"
                label="{{ __('auth.fields.password') }}"
                autocomplete="new-password"
                :show-lock-icon="false"
            />

            <x-password-input
                id="password_confirmation"
                wire:model="password_confirmation"
                label="{{ __('auth.fields.password_confirmation') }}"
                autocomplete="new-password"
                :show-lock-icon="false"
            />

            <x-primary-button
                type="submit"
                class="mt-2 w-full disabled:bg-intense-cocoa/40"
            >
                {{ __('auth.reset_password.submit') }}
            </x-primary-button>
        </form>
    </div>
</div>
