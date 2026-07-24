<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\DTOs\Reviews\UpsertReviewDTO;
use App\Exceptions\Reviews\InvalidReviewRatingException;
use App\Exceptions\Reviews\ReviewAlreadyExistsException;
use App\Exceptions\Reviews\ReviewNotAllowedException;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\Reviews\ReviewEligibilityService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateReviewAction
{
    public function __construct(
        private readonly ReviewEligibilityService $eligibilityService,
    ) {}

    /**
     * @throws ReviewNotAllowedException
     * @throws ReviewAlreadyExistsException
     * @throws InvalidReviewRatingException
     * @throws ValidationException
     * @throws Throwable
     */
    public function __invoke(User $user, UpsertReviewDTO $dto): Review
    {
        $this->assertValidPayload($dto);

        $product = Product::query()->findOrFail($dto->productId);

        if (Review::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists()
        ) {
            throw ReviewAlreadyExistsException::forProduct();
        }

        if (! $this->eligibilityService->hasEligiblePurchase($user, $product)) {
            throw ReviewNotAllowedException::notEligible();
        }

        return DB::transaction(function () use ($user, $product, $dto): Review {
            return Review::query()->create([
                'product_id' => $product->id,
                'user_id' => $user->id,
                'rating' => $dto->rating,
                'comment' => $dto->comment,
                'is_approved' => false,
                'is_verified_purchase' => true,
            ]);
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
