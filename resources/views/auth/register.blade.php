<x-layouts::auth :title="'Leen Handbags | '.__('register.meta.page_title')">
    <div class="w-full bg-soft-sand p-8 shadow-ambient md:p-10">
        <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
            {{ __('register.title') }}
        </h1>
        <p class="mt-2 text-sm text-intense-cocoa/70">
            {{ __('register.subtitle') }}
        </p>

        @if (session('status'))
            <p role="status" data-register-status class="mt-6 border border-soft-gold/30 bg-soft-sand px-4 py-3 text-sm text-intense-cocoa">
                {{ session('status') }}
            </p>
        @endif

        @if ($errors->any())
            <p role="alert" data-register-error class="mt-6 border border-error/20 bg-error/5 px-4 py-3 text-sm text-error">
                {{ $errors->first() }}
            </p>
        @endif

        <form
            method="POST"
            action="/register"
            novalidate
            x-data="{
                submitting: false,
                name: @js(old('name', '')),
                email: @js(old('email', '')),
                password: '',
                passwordConfirmation: '',
                termsAccepted: @js((bool) old('terms')),
                nameTouched: false,
                emailTouched: false,
                passwordTouched: false,
                confirmationTouched: false,
                termsTouched: false,
                get emailValid() {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
                },
                get passwordScore() {
                    return [this.password.length >= 8, /[A-Z]/.test(this.password), /[0-9]/.test(this.password)]
                        .filter(Boolean).length;
                },
                get confirmationEmpty() {
                    return this.passwordConfirmation.trim() === '';
                },
                get passwordsMatch() {
                    return this.password === this.passwordConfirmation;
                },
                get formValid() {
                    return this.name.trim() !== ''
                        && this.emailValid
                        && this.password.trim() !== ''
                        && ! this.confirmationEmpty
                        && this.passwordsMatch
                        && this.termsAccepted;
                },
            }"
            x-on:submit="submitting = true"
            class="mt-6 space-y-5"
        >
            @csrf

            {{-- Name --}}
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('register.fields.name') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                    </span>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        x-model="name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        :readonly="submitting"
                        :class="{ 'opacity-60': submitting }"
                        x-on:blur="nameTouched = true"
                        placeholder="{{ __('register.placeholders.name') }}"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('name') border-error @else border-intense-cocoa/40 @enderror"
                    >
                </div>
                <p x-show="nameTouched && name.trim() === ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.name_required') }}
                </p>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('register.fields.email') }}
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
                        x-model="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        :readonly="submitting"
                        :class="{ 'opacity-60': submitting }"
                        x-on:blur="emailTouched = true"
                        placeholder="{{ __('register.placeholders.email') }}"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('email') border-error @else border-intense-cocoa/40 @enderror"
                    >
                </div>
                <p x-show="emailTouched && !emailValid && email.trim() === ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.email_required') }}
                </p>
                <p x-show="emailTouched && !emailValid && email.trim() !== ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.email_invalid') }}
                </p>
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('register.fields.password') }}
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
                        x-model="password"
                        autocomplete="new-password"
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
                        :aria-label="show ? '{{ __('register.actions.hide_password') }}' : '{{ __('register.actions.show_password') }}'"
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
                <p x-show="passwordTouched && password.trim() === ''" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.password_required') }}
                </p>

                {{-- Strength meter (decorative, non-authoritative) --}}
                <div class="mt-2 flex gap-1" x-show="password.length > 0" x-cloak aria-hidden="true">
                    <template x-for="i in 3" :key="i">
                        <span class="h-1 flex-1 transition-colors"
                              :class="i > passwordScore ? 'bg-intense-cocoa/15'
                                  : (passwordScore === 1 ? 'bg-error' : passwordScore === 2 ? 'bg-soft-gold' : 'bg-success')"></span>
                    </template>
                </div>
                <p class="mt-1 text-sm" role="status" aria-live="polite">
                    <span x-show="passwordScore <= 1" x-cloak class="text-intense-cocoa">{{ __('register.strength.weak') }}</span>
                    <span x-show="passwordScore === 2" x-cloak class="text-intense-cocoa">{{ __('register.strength.medium') }}</span>
                    <span x-show="passwordScore === 3" x-cloak class="text-intense-cocoa">{{ __('register.strength.strong') }}</span>
                </p>
            </div>

            {{-- Password confirmation --}}
            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('register.fields.password_confirmation') }}
                </label>
                <div class="relative" x-data="{ show: false }">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                    </span>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        x-model="passwordConfirmation"
                        autocomplete="new-password"
                        required
                        :readonly="submitting"
                        x-on:blur="confirmationTouched = true"
                        x-bind:class="{ '!border-error': confirmationTouched && (confirmationEmpty || ! passwordsMatch), 'opacity-60': submitting }"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-11 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('password_confirmation') border-error @else border-intense-cocoa/40 @enderror"
                    >
                    <button
                        type="button"
                        x-on:click="show = !show"
                        :aria-pressed="show"
                        :aria-label="show ? '{{ __('register.actions.hide_password') }}' : '{{ __('register.actions.show_password') }}'"
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
                <p x-show="confirmationTouched && confirmationEmpty" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.password_confirmation_required') }}
                </p>
                <p x-show="confirmationTouched && ! confirmationEmpty && ! passwordsMatch" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.password_mismatch') }}
                </p>
            </div>

            {{-- Phone (optional) --}}
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-intense-cocoa">
                    {{ __('register.fields.phone_optional') }}
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-intense-cocoa/60">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                    </span>
                    <input
                        id="phone"
                        name="phone"
                        type="tel"
                        value="{{ old('phone') }}"
                        autocomplete="tel"
                        :readonly="submitting"
                        :class="{ 'opacity-60': submitting }"
                        placeholder="{{ __('register.placeholders.phone') }}"
                        class="w-full border bg-silk-cream py-3 pl-11 pr-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none @error('phone') border-error @else border-intense-cocoa/40 @enderror"
                    >
                </div>
            </div>

            {{-- Terms and conditions --}}
            <div>
                <label class="flex items-start gap-3">
                    <span class="relative mt-1 flex items-center justify-center">
                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            value="1"
                            x-model="termsAccepted"
                            x-on:blur="termsTouched = true"
                            :disabled="submitting"
                            class="peer h-4 w-4 appearance-none border border-intense-cocoa bg-white checked:border-intense-cocoa checked:bg-intense-cocoa focus:outline-none disabled:cursor-not-allowed disabled:opacity-70"
                        >
                        <svg class="pointer-events-none absolute h-3 w-3 text-silk-cream opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <span class="text-sm text-intense-cocoa/80">
                        {{ __('register.terms.prefix') }}
                        @if (Route::has('terms'))
                            <a href="{{ route('terms') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">{{ __('register.terms.terms_link') }}</a>
                        @else
                            <span class="font-medium text-intense-cocoa">{{ __('register.terms.terms_link') }}</span>
                        @endif
                        {{ __('register.terms.connector') }}
                        @if (Route::has('privacy'))
                            <a href="{{ route('privacy') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">{{ __('register.terms.privacy_link') }}</a>
                        @else
                            <span class="font-medium text-intense-cocoa">{{ __('register.terms.privacy_link') }}</span>
                        @endif
                    </span>
                </label>
                <p x-show="termsTouched && ! termsAccepted" x-cloak class="mt-1 text-sm text-error">
                    {{ __('register.errors.terms_required') }}
                </p>
            </div>

            <button
                type="submit"
                :disabled="submitting || ! formValid"
                class="mt-2 flex h-12 w-full items-center justify-center gap-2 bg-intense-cocoa text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                <span x-show="!submitting">{{ __('register.actions.submit') }}</span>
                <span x-show="submitting" x-cloak>{{ __('register.actions.submitting') }}</span>
            </button>

            <p class="mt-4 text-center text-sm text-intense-cocoa/70">
                {{ __('register.links.have_account') }}
                <a href="{{ route('login') }}" class="font-medium text-intense-cocoa underline underline-offset-2 transition-colors hover:text-soft-gold">
                    {{ __('register.links.login') }}
                </a>
            </p>
        </form>
    </div>
</x-layouts::auth>
