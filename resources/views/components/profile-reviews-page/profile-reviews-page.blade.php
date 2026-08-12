<x-partials.account-shell active="reviews">
    <div class="w-full max-w-4xl space-y-8">
        <div>
            <h1 class="font-[family-name:var(--font-chillax)] text-2xl font-semibold text-intense-cocoa">
                {{ __('account.reviews.title') }}
            </h1>
            <p class="mt-2 text-sm text-intense-cocoa/70">
                {{ __('account.reviews.subtitle') }}
            </p>
        </div>

        @if ($statusMessage)
            <p class="border border-intense-cocoa/20 bg-intense-cocoa/5 px-4 py-3 text-sm text-intense-cocoa">
                {{ $statusMessage }}
            </p>
        @endif

        @if ($errorMessage)
            <p role="alert" class="border border-error/20 bg-error/5 px-4 py-3 text-sm text-error">
                {{ $errorMessage }}
            </p>
        @endif

        <div class="space-y-4">
            @forelse ($reviews as $review)
                <x-section-card.section-card wire:key="review-{{ $review->id }}" data-review-card="{{ $review->id }}">
                    @if ($editingId === $review->id)
                        <form wire:submit="save" class="space-y-4">
                            <p class="font-semibold text-intense-cocoa">{{ $review->product->name }}</p>

                            <div>
                                <label for="rating" class="mb-1 block text-sm font-medium text-intense-cocoa">
                                    {{ __('account.reviews.fields.rating') }}
                                </label>
                                <select id="rating" wire:model="rating" class="w-full border border-intense-cocoa/40 bg-silk-cream px-3 py-2 text-sm text-intense-cocoa focus:border-intense-cocoa focus:outline-none">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('rating')
                                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="comment" class="mb-1 block text-sm font-medium text-intense-cocoa">
                                    {{ __('account.reviews.fields.comment') }}
                                </label>
                                <textarea id="comment" wire:model="comment" rows="4" class="w-full border border-intense-cocoa/40 bg-silk-cream px-3 py-2 text-sm text-intense-cocoa focus:border-intense-cocoa focus:outline-none"></textarea>
                                @error('comment')
                                    <p class="mt-1 text-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col items-center justify-center gap-3 pt-2 sm:flex-row">
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                    class="h-11 w-full min-w-[180px] bg-intense-cocoa px-5 text-xs font-semibold uppercase tracking-wider text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa focus:outline-none sm:w-auto"
                                >
                                    {{ __('account.reviews.save') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="cancelEdit"
                                    class="h-11 w-full min-w-[180px] border border-intense-cocoa/30 px-5 text-xs font-semibold uppercase tracking-wider text-intense-cocoa transition-colors duration-200 hover:bg-intense-cocoa hover:text-silk-cream focus:outline-none sm:w-auto"
                                >
                                    {{ __('account.reviews.cancel') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-4">
                            {{-- 1. Status badge & Date on top --}}
                            <div class="flex items-center justify-between gap-2 border-b border-intense-cocoa/10 pb-3">
                                <span class="inline-flex items-center border {{ $review->is_approved ? 'border-intense-cocoa bg-intense-cocoa text-silk-cream' : 'border-soft-gold/50 bg-soft-sand text-intense-cocoa' }} px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-widest">
                                    {{ $review->is_approved ? __('account.reviews.status.approved') : __('account.reviews.status.pending') }}
                                </span>
                                <time class="text-xs text-intense-cocoa/50" datetime="{{ $review->created_at?->toIso8601String() }}">
                                    {{ $review->created_at?->format('d/m/Y') }}
                                </time>
                            </div>

                            {{-- 2. Product Title & Variant Chips (with light border) --}}
                            <div class="space-y-2">
                                <h3 class="font-[family-name:var(--font-chillax)] text-lg font-semibold text-intense-cocoa">
                                    <a href="{{ route('products.show', $review->product->slug) }}" class="transition-colors duration-200 hover:text-soft-gold">
                                        {{ $review->product->name }}
                                    </a>
                                </h3>

                                @if ($review->purchased_variants && count($review->purchased_variants) > 0)
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($review->purchased_variants as $variant)
                                            <span class="inline-flex items-center gap-1 border border-intense-cocoa/20 bg-soft-sand/60 px-2.5 py-1 text-xs text-intense-cocoa/80">
                                                @if ($variant['color'])
                                                    <span class="font-medium">{{ $variant['color'] }}</span>
                                                @endif
                                                @if ($variant['size'])
                                                    <span class="font-medium">{{ $variant['size'] }}</span>
                                                @endif
                                                @if ($variant['sku'])
                                                    <span class="text-intense-cocoa/50">({{ $variant['sku'] }})</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif ($purchasedVariant = $purchasedVariants->get($review->product_id))
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center gap-1 border border-intense-cocoa/20 bg-soft-sand/60 px-2.5 py-1 text-xs text-intense-cocoa/80">
                                            <span class="font-medium">{{ __('account.orders.sku_label') }}: {{ $purchasedVariant->sku }}</span>
                                            @if ($purchasedVariant->variant_label)
                                                <span>· {{ $purchasedVariant->variant_label }}</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                @if ($reviewsWithNewVariants->contains($review->id))
                                    <p class="mt-2 rounded-none border border-soft-gold/40 bg-soft-sand/60 px-3 py-2 text-xs text-intense-cocoa/70">
                                        {{ __('reviews.ui.new_variants_available') }}
                                    </p>
                                @endif
                            </div>

                            {{-- 3. Rating & Review Text --}}
                            <div class="space-y-2">
                                <div class="flex items-center gap-1" role="img" aria-label="{{ __('account.reviews.rating_aria', ['rating' => $review->rating]) }}">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 {{ $i <= $review->rating ? 'text-soft-gold' : 'text-intense-cocoa/20' }}" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.445a1 1 0 0 0-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 0 0-.363-1.118L2.63 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.958Z" />
                                        </svg>
                                    @endfor
                                </div>

                                @if ($review->comment)
                                    <p class="text-sm leading-relaxed text-intense-cocoa/80">{{ $review->comment }}</p>
                                @endif
                            </div>
                            {{-- 4. Action Buttons at the bottom --}}
                            <div class="flex flex-wrap items-center justify-center gap-2 border-t border-intense-cocoa/10 pt-4 sm:justify-end">
                                @if ($confirmingDeleteId === $review->id)
                                    <div class="flex w-full flex-col items-center justify-center gap-3 border border-error/20 bg-error/5 p-3 text-center text-xs sm:flex-row sm:justify-between">
                                        <span class="font-medium text-intense-cocoa">{{ __('account.reviews.confirm_delete_prompt') }}</span>
                                        <div class="flex items-center justify-center gap-2">
                                            <button
                                                type="button"
                                                wire:click="delete({{ $review->id }})"
                                                class="border border-error/30 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-error transition-all duration-200 hover:border-error hover:bg-error hover:text-silk-cream"
                                            >
                                                {{ __('account.reviews.delete') }}
                                            </button>
                                            <button
                                                type="button"
                                                wire:click="cancelDeleteConfirmation"
                                                class="border border-intense-cocoa/30 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-intense-cocoa transition-all duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
                                            >
                                                {{ __('account.reviews.cancel') }}
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        wire:click="edit({{ $review->id }})"
                                        class="inline-flex items-center gap-1.5 border border-intense-cocoa/20 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa hover:bg-intense-cocoa hover:text-silk-cream"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                            <path d="m5.433 13.917 1.262-3.155A4 4 0 0 1 7.58 9.42l6.92-6.918a2.121 2.121 0 0 1 3 3l-6.92 6.918c-.383.383-.84.685-1.343.886l-3.154 1.262a.5.5 0 0 1-.65-.65Z" />
                                        </svg>
                                        {{ __('account.reviews.edit') }}
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $review->id }})"
                                        class="inline-flex items-center gap-1.5 border border-error/30 px-3 py-1.5 text-xs font-semibold uppercase tracking-wider text-error transition-colors duration-200 hover:border-error hover:bg-error hover:text-silk-cream"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Zm3.34 0a.75.75 0 0 1 .75.72v6.5a.75.75 0 0 1-1.5 0v-6.5a.75.75 0 0 1 .75-.72Z" clip-rule="evenodd" />
                                        </svg>
                                        {{ __('account.reviews.delete') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </x-section-card.section-card>
            @empty
                <x-partials.account-empty-state
                    :title="__('account.reviews.empty_title')"
                    :message="__('account.reviews.empty')"
                    :cta-label="__('account.reviews.empty_cta')"
                    :cta-href="route('products.index')"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-16 w-16 text-intense-cocoa/40" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                    </svg>
                </x-partials.account-empty-state>
            @endforelse
        </div>

        <x-pagination :paginator="$reviews" class="mt-8" />
    </div>
</x-partials.account-shell>
