<?php

declare(strict_types=1);

use App\Actions\Reviews\DeleteReviewAction;
use App\Actions\Reviews\UpdateReviewAction;
use App\DTOs\Reviews\UpsertReviewDTO;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront'), Title('Leen Handbags | Mis reseñas')] class extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public int $rating = 5;

    public string $comment = '';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function with(): array
    {
        return [
            'reviews' => Review::query()
                ->ownedBy((int) Auth::id())
                ->with('product')
                ->latest()
                ->paginate(10),
        ];
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

            return;
        }

        if ($this->editingId === $reviewId) {
            $this->cancelEdit();
        }

        $this->statusMessage = __('account.reviews.deleted');
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'rating', 'comment']);
        $this->rating = 5;
    }
};
