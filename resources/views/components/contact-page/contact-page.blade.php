@php
    // Captured up-front: Blade's @error directive locally overwrites/unsets a
    // variable literally named $message, which collides with this component's
    // own `message` field (textarea content). Reading via this alias avoids
    // the collision regardless of which @error blocks run below.
    $messageBody = $message;
@endphp
<div class="py-8 lg:py-12">
    {{-- Breadcrumb --}}
    <x-breadcrumb.breadcrumb :items="[
        ['label' => __('contact.breadcrumb.home'), 'href' => route('home')],
        ['label' => __('contact.breadcrumb.contact')],
    ]"></x-breadcrumb.breadcrumb>

    <div class="mx-auto max-w-storefront px-margin-mobile lg:px-margin-desktop">
        <x-page-header
            title="{{ __('contact.title') }}"
            subtitle="{{ __('contact.subtitle') }}"
            size="4xl"
        />

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-5 lg:gap-12">
            {{-- Static contact info column (R1, R2) --}}
            <x-section-card.section-card tag="section" class="lg:col-span-2" aria-labelledby="contact-info-heading">
                <h2 id="contact-info-heading" class="font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                    {{ __('contact.info.heading') }}
                </h2>

                <ul class="mt-6 space-y-5 text-body-md text-intense-cocoa">
                    {{-- Email --}}
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 text-intense-cocoa" aria-hidden="true">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0a2.25 2.25 0 0 0-2.25-2.25h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                        </span>
                        <p>
                            <span class="block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('contact.info.email_label') }}</span>
                            <a href="mailto:{{ config('ecommerce.contact.public_email', 'hola@leenhandbags.com') }}" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">{{ config('ecommerce.contact.public_email', 'hola@leenhandbags.com') }}</a>
                        </p>
                    </li>

                    {{-- Phone --}}
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 text-intense-cocoa" aria-hidden="true">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                            </svg>
                        </span>
                        <p>
                            <span class="block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('contact.info.phone_label') }}</span>
                            <a href="tel:{{ config('ecommerce.contact.phone_raw', '+573001234567') }}" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">{{ config('ecommerce.contact.phone', '+57 300 123 4567') }}</a>
                        </p>
                    </li>

                    {{-- WhatsApp --}}
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 text-intense-cocoa" aria-hidden="true">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                <path d="M12.001 2C6.478 2 2 6.477 2 12c0 1.865.51 3.649 1.475 5.209L2 22l4.938-1.446A9.947 9.947 0 0 0 12.001 22C17.523 22 22 17.523 22 12S17.523 2 12.001 2zm0 18.001a8.02 8.02 0 0 1-4.315-1.255l-.31-.184-3.226.944.955-3.146-.202-.322A7.996 7.996 0 0 1 4.001 12c0-4.412 3.588-8 8-8 4.411 0 8 3.588 8 8s-3.589 8.001-8 8.001z" />
                            </svg>
                        </span>
                        <p>
                            <span class="block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('contact.info.whatsapp_label') }}</span>
                            <a href="{{ config('ecommerce.contact.whatsapp_url', 'https://wa.me/573001234567') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">{{ config('ecommerce.contact.whatsapp', '+57 300 123 4567') }}</a>
                        </p>
                    </li>

                    {{-- Business hours --}}
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 text-intense-cocoa" aria-hidden="true">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <p>
                            <span class="block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('contact.info.hours_label') }}</span>
                            <span class="font-medium text-intense-cocoa">{{ __('contact.info.hours_value') }}</span>
                        </p>
                    </li>

                    {{-- Social --}}
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 text-intense-cocoa" aria-hidden="true">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                            </svg>
                        </span>
                        <p>
                            <span class="block text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa/60">{{ __('contact.info.social_label') }}</span>
                            <span class="mt-1 flex items-center gap-3">
                                <a href="{{ config('ecommerce.contact.social.instagram', 'https://instagram.com') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">{{ __('contact.info.instagram') }}</a>
                                <span aria-hidden="true" class="text-intense-cocoa/30">/</span>
                                <a href="{{ config('ecommerce.contact.social.tiktok', 'https://tiktok.com') }}" target="_blank" rel="noopener noreferrer" class="font-medium text-intense-cocoa transition-colors hover:text-soft-gold">{{ __('contact.info.tiktok') }}</a>
                            </span>
                        </p>
                    </li>
                </ul>
            </x-section-card.section-card>

            {{-- Contact form (R3, R5, R6, R10-R15) --}}
            <x-section-card.section-card tag="section" class="lg:col-span-3" aria-labelledby="contact-form-heading">
                <h2 id="contact-form-heading" class="font-[family-name:var(--font-chillax)] text-xl font-semibold text-intense-cocoa">
                    {{ __('contact.form.heading') }}
                </h2>

                @if ($errorMessage)
                    <x-alert type="error" data-contact-error class="mt-4">
                        {{ $errorMessage }}
                        <a href="mailto:{{ config('ecommerce.contact.public_email') }}" class="font-medium underline underline-offset-2">
                            {{ config('ecommerce.contact.public_email') }}
                        </a>
                    </x-alert>
                @endif

                @if ($sent)
                    <div role="status" data-contact-success class="mt-6 flex flex-col items-center gap-3 text-center">
                        <svg class="h-12 w-12 text-soft-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <h3 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                            {{ __('contact.success.title') }}
                        </h3>
                        <p class="max-w-sm text-body-md text-intense-cocoa/70">
                            {{ __('contact.success.message') }}
                        </p>
                        <x-secondary-button
                            type="button"
                            wire:click="sendAnother"
                            class="h-11 px-6"
                        >
                            {{ __('contact.success.new_message') }}
                        </x-secondary-button>
                    </div>
                @else
                    <form wire:submit="submit" class="mt-4 space-y-5">
                        <x-form-input
                            id="contact-name"
                            name="name"
                            type="text"
                            wire:model.blur="name"
                            label="{{ __('contact.form.name') }}"
                            placeholder="{{ __('contact.form.placeholders.name') }}"
                            autocomplete="name"
                            required
                            maxlength="255"
                            aria-required="true"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                        />

                        <x-form-input
                            id="contact-email"
                            name="email"
                            type="email"
                            wire:model.blur="email"
                            label="{{ __('contact.form.email') }}"
                            placeholder="{{ __('contact.form.placeholders.email') }}"
                            autocomplete="email"
                            required
                            maxlength="255"
                            aria-required="true"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                        />

                        <x-form-input
                            id="contact-subject"
                            name="subject"
                            type="text"
                            wire:model.blur="subject"
                            label="{{ __('contact.form.subject') }}"
                            placeholder="{{ __('contact.form.placeholders.subject') }}"
                            required
                            maxlength="150"
                            aria-required="true"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                        />

                        {{-- Message + Alpine-local live character counter (R5, R12, A5) --}}
                        <div
                            x-data="{
                                count: {{ strlen($messageBody) }},
                                max: 1000,
                                template: @js(__('contact.form.counter', ['count' => ':count', 'max' => ':max'])),
                                get label() { return this.template.replace(':count', this.count).replace(':max', this.max) },
                            }"
                        >
                            <label for="contact-message" class="mb-1 block text-sm font-medium text-intense-cocoa">
                                {{ __('contact.form.message') }}
                            </label>
                            <textarea
                                id="contact-message"
                                wire:model.blur="message"
                                maxlength="1000"
                                rows="6"
                                placeholder="{{ __('contact.form.placeholders.message') }}"
                                required
                                aria-required="true"
                                aria-describedby="contact-message-counter contact-message-error"
                                aria-invalid="@error('message') true @else false @enderror"
                                wire:loading.attr="disabled"
                                wire:target="submit"
                                x-on:input="count = $event.target.value.length"
                                class="w-full border bg-silk-cream px-3 py-3 text-body-md text-intense-cocoa transition-colors hover:border-intense-cocoa focus:border-intense-cocoa focus:outline-none focus-visible:ring-2 focus-visible:ring-soft-gold disabled:cursor-not-allowed disabled:opacity-60 @error('message') border-error @else border-intense-cocoa/40 @enderror"
                            ></textarea>
                            <p
                                id="contact-message-counter"
                                data-contact-counter
                                class="mt-1 text-right text-sm"
                                :class="count >= max ? 'text-error font-medium' : 'text-intense-cocoa/60'"
                                x-text="label"
                            >{{ __('contact.form.counter', ['count' => strlen($messageBody), 'max' => 1000]) }}</p>
                            @error('message') <p id="contact-message-error" data-error="message" class="mt-1 text-sm text-error">{{ $message }}</p> @enderror
                        </div>

                        <x-primary-button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            class="w-full gap-2 text-label-caps lg:w-auto lg:px-10"
                        >
                            <svg wire:loading wire:target="submit" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="submit">{{ __('contact.form.submit') }}</span>
                            <span wire:loading wire:target="submit">{{ __('contact.form.sending') }}</span>
                        </x-primary-button>
                    </form>
                @endif
            </x-section-card.section-card>
        </div>

        {{-- FAQ CTA (R7) --}}
        <section aria-labelledby="contact-faq-heading" class="mt-12 flex flex-col items-center gap-3 bg-soft-sand px-8 py-10 text-center lg:mt-16">
            <h2 id="contact-faq-heading" class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('contact.faq.heading') }}
            </h2>
            <p class="max-w-md text-body-md text-intense-cocoa/70">
                {{ __('contact.faq.body') }}
            </p>
            <x-secondary-button
                tag="a"
                href="{{ url('/faq') }}"
                class="mt-2 h-11 px-6"
            >
                {{ __('contact.faq.cta') }}
            </x-secondary-button>
        </section>
    </div>
</div>
