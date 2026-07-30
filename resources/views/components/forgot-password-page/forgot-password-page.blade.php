<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-2 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.forgot_password.title') }}
        </h1>
        <p class="mb-6 text-sm text-intense-cocoa/70">
            {{ __('auth.forgot_password.intro') }}
        </p>

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        @if ($statusMessage)
            <p class="mb-4 border border-intense-cocoa/20 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" role="status">
                {{ $statusMessage }}
            </p>
        @endif

        <form wire:submit="sendResetLink" class="space-y-4">
            @include('components.checkout-page.partials.text-field', [
                'field' => 'email',
                'label' => 'auth.fields.email',
                'wireModel' => 'wire:model="email"',
                'type' => 'email',
            ])

            <x-primary-button
                type="submit"
                class="mt-2 w-full disabled:bg-intense-cocoa/40"
            >
                {{ __('auth.forgot_password.submit') }}
            </x-primary-button>
        </form>

        <p class="mt-6 text-sm text-intense-cocoa/80">
            <a href="{{ route('login') }}" class="font-medium text-intense-cocoa transition-colors hover:underline">
                {{ __('auth.forgot_password.back_to_login') }}
            </a>
        </p>
    </div>
</div>
