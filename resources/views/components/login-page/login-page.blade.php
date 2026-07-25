<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-6 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.login.title') }}
        </h1>

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        <form wire:submit="login" class="space-y-4">
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

            <label class="flex items-center gap-2 text-sm text-intense-cocoa/80">
                <input type="checkbox" wire:model="remember" class="border-intense-cocoa/40">
                {{ __('auth.login.remember') }}
            </label>

            <button
                type="submit"
                class="mt-2 flex h-12 w-full items-center justify-center bg-intense-cocoa text-sm font-semibold text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:bg-intense-cocoa/40"
            >
                {{ __('auth.login.submit') }}
            </button>
        </form>

        <div class="mt-6 flex flex-col gap-2 text-sm text-intense-cocoa/80">
            <a href="{{ route('password.request') }}" class="transition-colors hover:text-intense-cocoa hover:underline">
                {{ __('auth.login.forgot_password') }}
            </a>
            <p>
                {{ __('auth.login.no_account') }}
                <a href="{{ route('register') }}" class="font-medium text-intense-cocoa transition-colors hover:underline">
                    {{ __('auth.login.register_link') }}
                </a>
            </p>
        </div>
    </div>
</div>
