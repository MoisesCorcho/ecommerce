<?php

declare(strict_types=1);

namespace App\Enums\Localization;

use Filament\Support\Contracts\HasLabel;

/**
 * Storefront languages. Stored values are ISO 639-1 codes and are the single
 * source of truth for which locales the application accepts.
 */
enum LocaleEnum: string implements HasLabel
{
    case Es = 'es';
    case En = 'en';

    public function getLabel(): string
    {
        return $this->label();
    }

    /**
     * Endonym — the language name written in that same language.
     *
     * Deliberately NOT resolved through __() even though the project
     * convention routes enum labels through translation files. Translating a
     * language name defeats the switcher: a visitor stranded in a language
     * they cannot read looks for their own language spelled their own way,
     * not for "Spanish" rendered in English.
     */
    public function label(): string
    {
        return match ($this) {
            self::Es => 'Español',
            self::En => 'English',
        };
    }

    /**
     * Resolve a stored preference that may be absent, stale, or tampered with.
     *
     * Session values, cookies and query input are all untrusted here: a locale
     * that was dropped from the enum must degrade to the configured default
     * rather than raising.
     */
    public static function tryFromValid(?string $value): ?self
    {
        return $value === null ? null : self::tryFrom($value);
    }
}
