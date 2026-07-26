<x-partials.account-shell active="reviews">
    <div class="w-full max-w-2xl space-y-8">
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
                <div wire:key="review-{{ $review->id }}" data-review-card="{{ $review->id }}" class="bg-soft-sand p-6 shadow-ambient">
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

                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="save"
                                    class="h-11 flex-1 bg-intense-cocoa text-label-caps font-semibold uppercase tracking-widest text-silk-cream transition-colors duration-200 hover:bg-soft-gold hover:text-intense-cocoa disabled:cursor-not-allowed disabled:opacity-70"
                                >
                                    {{ __('account.reviews.save') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="cancelEdit"
                                    class="h-11 flex-1 border border-intense-cocoa/40 text-label-caps font-semibold uppercase tracking-widest text-intense-cocoa transition-colors duration-200 hover:border-intense-cocoa"
                                >
                                    {{ __('account.reviews.cancel') }}
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-intense-cocoa">{{ $review->product->name }}</p>
                                @if ($review->purchased_variants && count($review->purchased_variants) > 0)
                                    <div class="mt-1 flex flex-wrap gap-1.5">
                                        @foreach ($review->purchased_variants as $variant)
                                            <span class="inline-flex items-center gap-1 text-xs text-intense-cocoa/50">
                                                @if ($variant['color'])
                                                    <span class="font-medium">{{ $variant['color'] }}</span>
                                                @endif
                                                @if ($variant['size'])
                                                    <span class="font-medium">{{ $variant['size'] }}</span>
                                                @endif
                                                @if ($variant['sku'])
                                                    <span>({{ $variant['sku'] }})</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @elseif ($purchasedVariant = $purchasedVariants->get($review->product_id))
                                    <p class="text-xs text-intense-cocoa/50">
                                        {{ __('account.orders.sku_label') }}: {{ $purchasedVariant->sku }}
                                        @if ($purchasedVariant->variant_label)
                                            · {{ $purchasedVariant->variant_label }}
                                        @endif
                                    </p>
                                @endif
                                @if ($reviewsWithNewVariants->contains($review->id))
                                    <p class="mt-2 rounded-sm border border-soft-gold/40 bg-soft-sand/60 px-3 py-2 text-xs text-intense-cocoa/70">
                                        {{ __('reviews.ui.new_variants_available') }}
                                    </p>
                                @endif
                                <div class="mt-1 flex items-center gap-1" role="img" aria-label="{{ __('account.reviews.rating_aria', ['rating' => $review->rating]) }}">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 {{ $i <= $review->rating ? 'text-soft-gold' : 'text-intense-cocoa/20' }}" aria-hidden="true">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 0 0-.363 1.118l1.287 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.445a1 1 0 0 0-1.176 0l-3.367 2.445c-.783.57-1.838-.196-1.538-1.118l1.287-3.957a1 1 0 0 0-.363-1.118L2.63 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.286-3.958Z" />
                                        </svg>
                                    @endfor
                                </div>
                                @if ($review->comment)
                                    <p class="mt-1 text-sm text-intense-cocoa/80">{{ $review->comment }}</p>
                                @endif
                                <span class="mt-2 inline-flex items-center border border-intense-cocoa/40 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-intense-cocoa">
                                    {{ $review->is_approved ? __('account.reviews.status.approved') : __('account.reviews.status.pending') }}
                                </span>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
                                <button type="button" wire:click="edit({{ $review->id }})" class="text-intense-cocoa underline underline-offset-2 hover:text-soft-gold">
                                    {{ __('account.reviews.edit') }}
                                </button>
                                @if ($confirmingDeleteId === $review->id)
                                    <div class="flex items-center gap-3">
                                        <span class="text-intense-cocoa/70">{{ __('account.reviews.confirm_delete_prompt') }}</span>
                                        <button
                                            type="button"
                                            wire:click="delete({{ $review->id }})"
                                            class="text-error underline underline-offset-2 hover:text-error/70"
                                        >
                                            {{ __('account.reviews.delete') }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="cancelDeleteConfirmation"
                                            class="text-intense-cocoa underline underline-offset-2 hover:text-soft-gold"
                                        >
                                            {{ __('account.reviews.cancel') }}
                                        </button>
                                    </div>
                                @else
                                    <button
                                        type="button"
                                        wire:click="confirmDelete({{ $review->id }})"
                                        class="text-error underline underline-offset-2 hover:text-error/70"
                                    >
                                        {{ __('account.reviews.delete') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
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

        {{ $reviews->links() }}
    </div>
</x-partials.account-shell>
