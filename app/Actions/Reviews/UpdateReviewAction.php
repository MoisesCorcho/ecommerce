<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\DTOs\Reviews\UpsertReviewDTO;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use App\Models\User;
use App\Services\Reviews\ReviewEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateReviewAction
{
    public function __construct(
        private readonly ReviewEligibilityService $eligibilityService,
    ) {}

    /**
     * @throws ReviewForbiddenException
     * @throws InvalidReviewRatingException
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(User $user, Review $review, UpsertReviewDTO $dto): Review
    {
        if ((int) $review->user_id !== (int) $user->id) {
            throw ReviewForbiddenException::notOwner();
        }

        $this->assertValidPayload($dto);

        $review->loadMissing('product');

        return DB::transaction(function () use ($user, $review, $dto): Review {
            $review->update([
                'rating' => $dto->rating,
                'comment' => $dto->comment,
                'is_approved' => false,
                'is_verified_purchase' => $this->eligibilityService->isVerifiedPurchase($user, $review->product),
            ]);

            return $review->fresh() ?? $review;
        });
    }

    /**
     * @throws InvalidReviewRatingException
     * @throws ValidationException
     */
    private function assertValidPayload(UpsertReviewDTO $dto): void
    {
        if ($dto->rating < 1 || $dto->rating > 5) {
            throw InvalidReviewRatingException::outOfRange();
        }

        if ($dto->comment !== null && mb_strlen($dto->comment) > 2000) {
            throw ValidationException::withMessages([
                'comment' => [__('reviews.errors.comment_too_long')],
            ]);
        }
    }
}
