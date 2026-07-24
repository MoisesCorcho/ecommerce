<x-layouts::auth :title="__('login.meta.page_title').' | '.config('app.name', 'Leen Handbags')">
    <div class="w-full border border-intense-cocoa/30 bg-soft-sand p-8 shadow-ambient md:p-10">
        <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
            {{ __('login.title') }}
        </h1>
        <p class="mt-2 text-sm text-intense-cocoa/70">
            {{ __('login.subtitle') }}
        </p>

        @if (session('status'))
            <p role="status" data-login-status class="mt-6 border border-soft-gold/30 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <p role="alert" data-login-error class="mt-6 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error">
                {{ $errors->first() }}
            </p>
        @endif

        <form
            method="POST"
            action="/login"
            novalidate
            x-data="{ submitting: false, emailTouched: false, emailValid: true, passwordTouched: false }"
            x-on:submit="submitting = true"
            class="mt-6 space-y-5"
        >
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('login.fields.email') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        x-ref="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        :readonly="submitting"
                        :class="{ 'opacity-60': submitting }"
                        x-on:blur="emailTouched = true; emailValid = $refs.email.value.trim() !== '' && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($refs.email.value)"
                        placeholder="{{ __('login.placeholders.email') }}"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('email') border-error @else border-intense-cocoa/40 @enderror"
                    >
                </div>
                {{-- No per-field @error text here by design (D7): the generic banner
                     above already renders $errors->first() once. Duplicating the same
                     credential message under the field would print it twice and leak
                     which field failed — the red border above is the only per-field
                     signal for a server-side error. --}}
                <p x-show="emailTouched && !emailValid && $refs.email.value.trim() === ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('login.errors.email_required') }}
                </p>
                <p x-show="emailTouched && !emailValid && $refs.email.value.trim() !== ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('login.errors.email_invalid') }}
                </p>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('login.fields.password') }}
                </label>
                <div class="relative" x-data="{ show: false }">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </span>
                    <input
                        id="password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        x-ref="password"
                        autocomplete="current-password"
                        required
                        :readonly="submitting"
                        :class="{ 'opacity-60': submitting }"
                        x-on:blur="passwordTouched = true"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-11 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('password') border-error @else border-intense-cocoa/40 @enderror"
                    >
                    <button
                        type="button"
                        x-on:click="show = !show"
                        :aria-pressed="show"
                        :aria-label="show ? '{{ __('login.actions.hide_password') }}' : '{{ __('login.actions.show_password') }}'"
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
                {{-- No per-field @error text here for SERVER credential errors (D7) — the
                     border swap above is the only per-field signal for those. This is a
                     distinct client-only required check, same category as email's above. --}}
                <p x-show="passwordTouched && $refs.password.value.trim() === ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('login.errors.password_required') }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="submitting"
                class="mt-2 flex h-12 w-full items-center justify-center gap-2 bg-intense-cocoa text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                <span x-show="!submitting">{{ __('login.actions.submit') }}</span>
                <span x-show="submitting" x-cloak>{{ __('login.actions.submitting') }}</span>
            </button>

            <div class="flex flex-col items-center gap-2 text-sm">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">
                        {{ __('login.links.forgot_password') }}
                    </a>
                @endif

                @if (Route::has('register'))
                    <p class="text-intense-cocoa/70">
                        {{ __('login.links.no_account') }}
                        <a href="{{ route('register') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">
                            {{ __('login.links.register') }}
                        </a>
                    </p>
                @endif
            </div>
        </form>
    </div>
</x-layouts::auth>
