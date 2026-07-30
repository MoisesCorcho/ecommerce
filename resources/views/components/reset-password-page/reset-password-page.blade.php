<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-6 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.reset_password.title') }}
        </h1>

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        <form wire:submit="resetPassword" class="space-y-4">
            @include('components.checkout-page.partials.text-field', [
                'field' => 'email',
                'label' => 'auth.fields.email',
                'wireModel' => 'wire:model="email"',
                'type' => 'email',
            ])
            @include('components.checkout-page.partials.text-field', [
                'field' => 'password',
                'label' => 'auth.fields.password',
                'wireModel' => 'wire:model="password"',
                'type' => 'password',
            ])
            @include('components.checkout-page.partials.text-field', [
                'field' => 'password_confirmation',
                'label' => 'auth.fields.password_confirmation',
                'wireModel' => 'wire:model="password_confirmation"',
                'type' => 'password',
            ])

            <x-primary-button
                type="submit"
                class="mt-2 w-full disabled:bg-intense-cocoa/40"
            >
                {{ __('auth.reset_password.submit') }}
            </x-primary-button>
        </form>
    </div>
</div>
