<x-section-card.section-card class="w-full md:p-10">
    <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
        {{ __('auth.register.title') }}
    </h1>
    <p class="mt-2 text-sm text-intense-cocoa/70">
        {{ __('auth.register.subtitle') }}
    </p>

    @if ($errorMessage)
        <p role="alert" class="mt-6 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error">
            {{ $errorMessage }}
        </p>
    @endif

    <form wire:submit="register" class="mt-6 space-y-5">
        {{-- Name --}}
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-intense-cocoa">
                {{ __('auth.fields.name') }}
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <input
                    id="name"
                    type="text"
                    wire:model="name"
                    autocomplete="name"
                    placeholder="{{ __('auth.register.placeholders.name') }}"
                    class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('name') border-error @else border-intense-cocoa/40 @enderror"
                >
            </div>
            @error('name')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Last name --}}
        <div>
            <label for="lastName" class="mb-1 block text-sm font-medium text-intense-cocoa">
                {{ __('auth.fields.last_name') }}
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </span>
                <input
                    id="lastName"
                    type="text"
                    wire:model="lastName"
                    autocomplete="family-name"
                    placeholder="{{ __('auth.register.placeholders.last_name') }}"
                    class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('lastName') border-error @else border-intense-cocoa/40 @enderror"
                >
            </div>
            @error('lastName')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-intense-cocoa">
                {{ __('auth.fields.email') }}
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </span>
                <input
                    id="email"
                    type="email"
                    wire:model="email"
                    autocomplete="email"
                    placeholder="{{ __('auth.register.placeholders.email') }}"
                    class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('email') border-error @else border-intense-cocoa/40 @enderror"
                >
            </div>
            @error('email')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div x-data="{ show: false, value: '' }">
            <label for="password" class="mb-1 block text-sm font-medium text-intense-cocoa">
                {{ __('auth.fields.password') }}
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <input
                    id="password"
                    wire:model="password"
                    :type="show ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="{{ __('auth.register.placeholders.password') }}"
                    x-on:input="value = $event.target.value"
                    class="w-full border bg-silk-cream py-3 pl-11 pr-11 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('password') border-error @else border-intense-cocoa/40 @enderror"
                >
                <button
                    type="button"
                    x-on:click="show = !show"
                    :aria-pressed="show"
                    :aria-label="show ? '{{ __('auth.register.hide_password') }}' : '{{ __('auth.register.show_password') }}'"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-intense-cocoa/60 transition-colors hover:text-soft-gold"
                >
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror

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
        </div>

        {{-- Password confirmation --}}
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-intense-cocoa">
                {{ __('auth.fields.password_confirmation') }}
            </label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </span>
                <input
                    id="password_confirmation"
                    wire:model="password_confirmation"
                    :type="show ? 'text' : 'password'"
                    autocomplete="new-password"
                    placeholder="{{ __('auth.register.placeholders.password_confirmation') }}"
                    class="w-full border bg-silk-cream py-3 pl-11 pr-11 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('password_confirmation') border-error @else border-intense-cocoa/40 @enderror"
                >
                <button
                    type="button"
                    x-on:click="show = !show"
                    :aria-pressed="show"
                    :aria-label="show ? '{{ __('auth.register.hide_password') }}' : '{{ __('auth.register.show_password') }}'"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-intense-cocoa/60 transition-colors hover:text-soft-gold"
                >
                    <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-sm text-error">{{ $message }}</p>
            @enderror
        </div>

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
