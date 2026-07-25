<div class="py-12 lg:py-16">
    <div class="mx-auto max-w-md px-margin-mobile lg:px-margin-desktop">
        <h1 class="mb-6 font-[family-name:var(--font-chillax)] text-3xl font-semibold tracking-tight text-intense-cocoa">
            {{ __('auth.register.title') }}
        </h1>

        @if ($errorMessage)
            <p class="mb-4 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                {{ $errorMessage }}
            </p>
        @endif

        <form wire:submit="register" class="space-y-4">
            @include('components.checkout-page.partials.text-field', [
                'field' => 'name',
                'label' => 'auth.fields.name',
                'wireModel' => 'wire:model="name"',
            ])
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

            <div>
                <label class="flex items-start gap-2 text-sm text-intense-cocoa/80">
                    <input type="checkbox" wire:model="terms" class="mt-0.5 border-intense-cocoa/40">
                    {{ __('auth.fields.terms') }}
                </label>
                @error('terms')
                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="mt-2 flex h-12 w-full items-center justify-center bg-intense-cocoa text-sm font-semibold text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:bg-intense-cocoa/40"
            >
                {{ __('auth.register.submit') }}
            </button>
        </form>

        <p class="mt-6 text-sm text-intense-cocoa/80">
            {{ __('auth.register.have_account') }}
            <a href="{{ route('login') }}" class="font-medium text-intense-cocoa transition-colors hover:underline">
                {{ __('auth.register.login_link') }}
            </a>
        </p>
    </div>
</div>
