<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use App\Models\User;
use App\Policies\ReviewPolicy;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteReviewAction
{
    public function __construct(private readonly ReviewPolicy $policy) {}

    /**
     * Hard-delete a review. Owner or admin only.
     *
     * @throws ReviewForbiddenException
     * @throws Throwable
     */
    public function __invoke(User $actor, Review $review): void
    {
        if (! $this->policy->delete($actor, $review)) {
            throw ReviewForbiddenException::notOwner();
        }

        DB::transaction(function () use ($review): void {
            $review->delete();
        });
    }
}
