<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use App\Models\User;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApproveReviewAction
{
    public function __construct(private readonly ReviewPolicy $policy) {}

    /**
     * @throws ReviewForbiddenException
     * @throws Throwable
     */
    public function __invoke(User $actor, Review $review): Review
    {
        if (! $this->policy->moderate($actor)) {
            throw ReviewForbiddenException::notOwner();
        }

        return DB::transaction(function () use ($review): Review {
            $review->update(['is_approved' => true]);

            return $review->fresh() ?? $review;
        });
    }
}
