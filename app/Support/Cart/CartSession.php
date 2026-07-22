<?php

declare(strict_types=1);

namespace App\Support\Cart;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Stable cart guest token stored in the Laravel session.
 */
final class CartSession
{
    public const string KEY = 'cart_session_id';

    public static function id(): ?string
    {
        $value = Session::get(self::KEY);

        return is_string($value) && $value !== '' ? $value : null;
    }

    public static function ensureId(): string
    {
        $existing = self::id();

        if ($existing !== null) {
            return $existing;
        }

        $id = (string) Str::uuid();
        Session::put(self::KEY, $id);

        return $id;
    }

    public static function setId(string $id): void
    {
        Session::put(self::KEY, $id);
    }

    public static function forget(): void
    {
        Session::forget(self::KEY);
    }
}
