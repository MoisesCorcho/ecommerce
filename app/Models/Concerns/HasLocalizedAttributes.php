<?php

declare(strict_types=1);

namespace App\Models\Concerns;

trait HasLocalizedAttributes
{
    /**
     * Get a translated attribute with smart fallback to default locale ('es').
     */
    public function getLocalizedAttribute(string $attribute, ?string $locale = null, string $fallback = 'es'): string
    {
        $locale = $locale ?? app()->getLocale();
        $translated = (string) ($this->getTranslation($attribute, $locale, false) ?? '');

        if ($translated !== '') {
            return $translated;
        }

        if ($locale === $fallback) {
            return '';
        }

        return (string) ($this->getTranslation($attribute, $fallback, false) ?? '');
    }

    /**
     * Get a nullable translated attribute with smart fallback to default locale ('es').
     */
    public function getLocalizedNullableAttribute(string $attribute, ?string $locale = null, string $fallback = 'es'): ?string
    {
        $value = $this->getLocalizedAttribute($attribute, $locale, $fallback);

        return $value !== '' ? $value : null;
    }
}
