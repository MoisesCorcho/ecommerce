<?php

declare(strict_types=1);

namespace App\Enums\Products;

use Filament\Support\Contracts\HasLabel;

enum SizeEnum: string implements HasLabel
{
    case Mini = 'mini';
    case Medium = 'medium';
    case Maxi = 'maxi';
    case OneSize = 'one_size';

    public function label(): string
    {
        return match ($this) {
            self::Mini => __('enums.size.mini'),
            self::Medium => __('enums.size.medium'),
            self::Maxi => __('enums.size.maxi'),
            self::OneSize => __('enums.size.one_size'),
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::Mini => 10,
            self::Medium => 20,
            self::Maxi => 30,
            self::OneSize => 40,
        };
    }
}
