<?php

declare(strict_types=1);

use App\Actions\Reviews\DeleteReviewAction;
use App\Actions\Reviews\UpdateReviewAction;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Enums\Orders\OrderStatusEnum;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Services\Reviews\ReviewVariantService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] class extends Component
{
    use WithPagination;

    public function render()
    {
        return $this->view();
    }

    public ?int $editingId = null;

    public ?int $confirmingDeleteId = null;

    public int $rating = 5;

    public string $comment = '';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function with(): array
    {
        $reviews = Review::query()
            ->ownedBy((int) Auth::id())
            ->with('product')
            ->latest()
            ->paginate(10);

        return [
            'reviews' => $reviews,
            'purchasedVariants' => $this->purchasedVariantsFor($reviews),
            'reviewsWithNewVariants' => $this->detectReviewsWithNewVariants($reviews),
        ];
    }

    /**
     * Detect reviews where the user has purchased new variants since the review was created/updated.
     *
     * @return Collection<int, int> review IDs with new variants
     */
    private function detectReviewsWithNewVariants(LengthAwarePaginator $reviews): Collection
    {
        if (Auth::guest()) {
            return collect();
        }

        $user = Auth::user();
        $variantService = app(ReviewVariantService::class);

        return $reviews->getCollection()
            ->filter(fn (Review $review) => $review->is_verified_purchase)
            ->filter(function (Review $review) use ($user, $variantService): bool {
                $product = $review->product ?? Product::find($review->product_id);
                if (! $product) {
                    return false;
                }

                $currentVariants = $variantService->getRecentPurchasedVariants($user, $product);
                $reviewedSkus = collect($review->purchased_variants ?? [])->pluck('sku')->filter()->values()->all();
                $currentSkus = collect($currentVariants)->pluck('sku')->filter()->values()->all();

                if (empty($reviewedSkus) || empty($currentSkus)) {
                    return false;
                }

                return $currentSkus !== $reviewedSkus;
            })
            ->pluck('id');
    }

    /**
     * Most recent eligible order item per product id, for the reviews on the current page.
     * Mirrors OrderStatusEnum::isEligibleForReview() (F07 D8), the single source of truth
     * for what counts as a real purchase for review purposes.
     *
     * @return Collection<int, OrderItem>
     */
    private function purchasedVariantsFor(LengthAwarePaginator $reviews): Collection
    {
        $productIds = $reviews->pluck('product_id')->unique()->values();

        if ($productIds->isEmpty()) {
            return collect();
        }

        $eligibleStatuses = array_values(array_filter(
            OrderStatusEnum::cases(),
            fn (OrderStatusEnum $status): bool => $status->isEligibleForReview(),
        ));

        return OrderItem::query()
            ->whereHas('order', fn ($query) => $query->where('user_id', Auth::id())->whereIn('status', $eligibleStatuses))
            ->whereHas('productVariant', fn ($query) => $query->whereIn('product_id', $productIds))
            ->with('productVariant')
            ->get()
            ->groupBy(fn (OrderItem $item) => $item->productVariant->product_id)
            ->map(fn (Collection $items) => $items->sortByDesc('created_at')->first());
    }

    public function edit(int $reviewId): void
    {
        $review = Review::query()->findOrFail($reviewId);

        if ((int) $review->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $this->editingId = $review->id;
        $this->rating = $review->rating;
        $this->comment = (string) ($review->comment ?? '');
        $this->confirmingDeleteId = null;
    }

    public function confirmDelete(int $reviewId): void
    {
        $this->confirmingDeleteId = $reviewId;
    }

    public function cancelDeleteConfirmation(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function save(UpdateReviewAction $action): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $review = Review::query()->findOrFail($this->editingId);

        $dto = UpsertReviewDTO::fromArray([
            'product_id' => $review->product_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        try {
            $action(Auth::user(), $review, $dto);
        } catch (ReviewForbiddenException|InvalidReviewRatingException $e) {
            $this->errorMessage = $e->getMessage();

            return;
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->statusMessage = __('account.reviews.updated');
        $this->cancelEdit();
    }

    public function delete(int $reviewId, DeleteReviewAction $action): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $review = Review::query()->findOrFail($reviewId);

        try {
            $action(Auth::user(), $review);
        } catch (ReviewForbiddenException $e) {
            $this->errorMessage = $e->getMessage();
            $this->confirmingDeleteId = null;

            return;
        }

        if ($this->editingId === $reviewId) {
            $this->cancelEdit();
        }

        $this->confirmingDeleteId = null;
        $this->statusMessage = __('account.reviews.deleted');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'rating', 'comment']);
        $this->rating = 5;
    }
};
