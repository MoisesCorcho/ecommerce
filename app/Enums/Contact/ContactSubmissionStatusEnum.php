<?php

declare(strict_types=1);

namespace App\Enums\Contact;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

enum ContactSubmissionStatusEnum: string implements HasColor, HasIcon, HasLabel
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => __('enums.contact_submission_status.new'),
            self::Read => __('enums.contact_submission_status.read'),
            self::Replied => __('enums.contact_submission_status.replied'),
            self::Archived => __('enums.contact_submission_status.archived'),
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::New => 'info',
            self::Read => 'warning',
            self::Replied => 'success',
            self::Archived => 'gray',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::New => Heroicon::Envelope,
            self::Read => Heroicon::EnvelopeOpen,
            self::Replied => Heroicon::ArrowUturnLeft,
            self::Archived => Heroicon::ArchiveBox,
        };
    }
}
