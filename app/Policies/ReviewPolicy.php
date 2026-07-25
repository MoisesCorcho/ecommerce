<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(?User $user, Review $review): bool
    {
        if ($review->is_approved) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($this->isAdmin($user)) {
            return true;
        }

        return (int) $review->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        return (int) $review->user_id === (int) $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ((int) $review->user_id === (int) $user->id) {
            return true;
        }

        return $this->isAdmin($user);
    }

    public function moderate(User $user): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
