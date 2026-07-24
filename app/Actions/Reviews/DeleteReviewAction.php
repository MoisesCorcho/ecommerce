<?php

declare(strict_types=1);

namespace App\Actions\Reviews;

use App\Exceptions\Reviews\ReviewForbiddenException;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteReviewAction
{
    /**
     * Hard-delete a review. Owner or admin only.
     *
     * @throws ReviewForbiddenException
     * @throws Throwable
     */
    public function __invoke(User $actor, Review $review): void
    {
        if (! $this->canDelete($actor, $review)) {
            throw ReviewForbiddenException::notOwner();
        }

        DB::transaction(function () use ($review): void {
            $review->delete();
        });
    }

    private function canDelete(User $actor, Review $review): bool
    {
        if ((int) $review->user_id === (int) $actor->id) {
            return true;
        }

        return $this->isAdmin($actor);
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
