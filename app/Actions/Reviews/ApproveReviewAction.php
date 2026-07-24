<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class ApproveReviewAction
{
    /**
     * @throws ReviewForbiddenException
     * @throws Throwable
     */
    public function __invoke(User $actor, Review $review): Review
    {
        if (! $this->isAdmin($actor)) {
            throw ReviewForbiddenException::notOwner();
        }

        return DB::transaction(function () use ($review): Review {
            $review->update(['is_approved' => true]);

            return $review->fresh() ?? $review;
        });
    }

    private function isAdmin(User $user): bool
    {
        $emails = config('ecommerce.admin_emails', []);

        if (! is_array($emails) || $emails === []) {
            return false;
        }

        return in_array($user->email, $emails, true);
    }
}
