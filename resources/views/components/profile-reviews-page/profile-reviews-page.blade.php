<div class="py-12 lg:py-16">
    <div class="mx-auto w-full max-w-2xl space-y-8 px-4">
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
                <div wire:key="review-{{ $review->id }}" class="bg-soft-sand p-6 shadow-ambient">
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
                                <p class="text-sm text-intense-cocoa/70">{{ __('account.reviews.fields.rating') }}: {{ $review->rating }}/5</p>
                                @if ($review->comment)
                                    <p class="mt-1 text-sm text-intense-cocoa/80">{{ $review->comment }}</p>
                                @endif
                                <span class="mt-2 inline-block text-xs font-semibold uppercase tracking-widest {{ $review->is_approved ? 'text-success' : 'text-soft-gold' }}">
                                    {{ $review->is_approved ? __('account.reviews.status.approved') : __('account.reviews.status.pending') }}
                                </span>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-2 text-sm">
                                <button type="button" wire:click="edit({{ $review->id }})" class="text-intense-cocoa underline underline-offset-2 hover:text-soft-gold">
                                    {{ __('account.reviews.edit') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $review->id }})"
                                    wire:confirm="{{ __('account.reviews.confirm_delete') }}"
                                    class="text-error underline underline-offset-2 hover:text-error/70"
                                >
                                    {{ __('account.reviews.delete') }}
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-intense-cocoa/70">{{ __('account.reviews.empty') }}</p>
            @endforelse
        </div>

        {{ $reviews->links() }}
    </div>
</div>
