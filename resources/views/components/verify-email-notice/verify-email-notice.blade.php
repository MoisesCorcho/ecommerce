<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile text-center lg:px-margin-desktop">
        <h1 class="mb-2 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.verify_email.title') }}
        </h1>
        <p class="mb-6 text-sm text-intense-cocoa/70">
            {{ __('auth.verify_email.intro', ['email' => auth()->user()->email]) }}
        </p>

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        @if ($resent)
            <p class="mb-4 border border-intense-cocoa/20 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa" role="status">
                {{ __('auth.verify_email.resent') }}
            </p>
        @endif

        <x-primary-button
            type="button"
            wire:click="resend"
            class="w-full disabled:bg-intense-cocoa/40"
        >
            {{ __('auth.verify_email.resend') }}
        </x-primary-button>
    </div>
</div>
