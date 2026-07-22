<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $order->user_id !== null && (int) $order->user_id === (int) $user->id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Storefront payment start (owner only; guests use signed URLs outside policy).
     */
    public function pay(User $user, Order $order): bool
    {
        return $order->user_id !== null && (int) $order->user_id === (int) $user->id;
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
