<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * Ensures only one item in a repeater can have a boolean flag set to true.
 *
 * Filament note: `$set('images.x.field', …, isAbsolute: true)` is incorrect for panel
 * forms because Livewire state is rooted at the schema path (usually `data.*`) and
 * absolute mode does **not** prefix that root. Prefer relative paths from the field
 * container (`../{siblingKey}.field`) or full paths derived from `getStatePath()`.
 *
 * @see https://filamentphp.com/docs/forms/overview#setting-the-state-of-another-field
 * @see https://filamentphp.com/docs/forms/overview#field-rendering (afterStateUpdatedJs)
 */
final class ExclusiveToggleInRepeater
{
    /**
     * @param  non-empty-string  $toggleField
     * @param  non-empty-string  $repeaterField  Repeater state name (e.g. "images")
     */
    public static function clearSiblings(
        Get $get,
        Set $set,
        Toggle $component,
        mixed $state,
        string $toggleField = 'is_primary',
        string $repeaterField = 'images',
    ): void {
        if (! self::isTruthy($state)) {
            return;
        }

        $currentPath = $component->getStatePath();
        // data.images.{itemKey}.is_primary
        $pattern = '/^(?P<repeaterPath>.+\.'.preg_quote($repeaterField, '/').')\.(?P<itemKey>[^.]+)\.'.preg_quote($toggleField, '/').'$/';

        if (! preg_match($pattern, $currentPath, $matches)) {
            // Fallback: climb parent components until we find a Repeater.
            self::clearSiblingsViaParentRepeater($set, $component, $toggleField);

            return;
        }

        $repeaterPath = $matches['repeaterPath'];
        $currentKey = $matches['itemKey'];
        $livewire = $component->getLivewire();
        $items = data_get($livewire, $repeaterPath);

        if (! is_array($items)) {
            return;
        }

        foreach (array_keys($items) as $itemKey) {
            $itemKey = (string) $itemKey;

            if ($itemKey === $currentKey) {
                continue;
            }

            // Full Livewire path — absolute mode does not add "data.", so pass the real path.
            $set("{$repeaterPath}.{$itemKey}.{$toggleField}", false, isAbsolute: true);
        }
    }

    /**
     * Client-side JS for instant exclusive toggle UX (no round-trip required for siblings).
     * Alpine scope provides $state, $get, $set, $statePath.
     *
     * @param  non-empty-string  $toggleField
     * @param  non-empty-string  $repeaterField
     */
    public static function afterStateUpdatedJs(
        string $toggleField = 'is_primary',
        string $repeaterField = 'images',
    ): string {
        $toggleFieldJs = json_encode($toggleField, JSON_THROW_ON_ERROR);
        $repeaterFieldJs = json_encode($repeaterField, JSON_THROW_ON_ERROR);

        return <<<JS
            if (\$state !== true && \$state !== 1 && \$state !== '1') {
                return
            }

            const toggleField = {$toggleFieldJs}
            const repeaterField = {$repeaterFieldJs}
            const statePath = String(\$statePath ?? '')
            const marker = '.' + repeaterField + '.'
            const markerIndex = statePath.lastIndexOf(marker)

            if (markerIndex === -1) {
                return
            }

            const afterMarker = statePath.slice(markerIndex + marker.length)
            const itemKey = afterMarker.split('.')[0]
            const repeaterPath = statePath.slice(0, markerIndex + marker.length - 1)

            if (! itemKey || ! repeaterPath) {
                return
            }

            const items = \$wire.\$get(repeaterPath) ?? {}

            for (const key of Object.keys(items)) {
                if (key === itemKey) {
                    continue
                }

                \$wire.\$set(repeaterPath + '.' + key + '.' + toggleField, false, false)
            }
            JS;
    }

    /**
     * Configure a Toggle that is exclusive within its parent repeater.
     *
     * @param  non-empty-string  $name
     * @param  non-empty-string  $repeaterField
     */
    public static function make(
        string $name = 'is_primary',
        ?string $label = null,
        ?string $helperText = null,
        string $repeaterField = 'images',
    ): Toggle {
        return Toggle::make($name)
            ->label($label ?? __('products.fields.primary_image'))
            ->helperText($helperText ?? __('products.helpers.primary_image_default'))
            ->default(false)
            ->inline(false)
            ->live()
            ->afterStateUpdatedJs(self::afterStateUpdatedJs($name, $repeaterField))
            ->afterStateUpdated(function (Get $get, Set $set, mixed $state, Toggle $component) use ($name, $repeaterField): void {
                self::clearSiblings(
                    get: $get,
                    set: $set,
                    component: $component,
                    state: $state,
                    toggleField: $name,
                    repeaterField: $repeaterField,
                );
            });
    }

    private static function clearSiblingsViaParentRepeater(Set $set, Toggle $component, string $toggleField): void
    {
        $repeater = self::findParentRepeater($component);

        if (! $repeater instanceof Repeater) {
            return;
        }

        $repeaterPath = $repeater->getStatePath();
        $currentPath = $component->getStatePath();
        $currentKey = null;

        if (str_starts_with($currentPath, $repeaterPath.'.')) {
            $relative = substr($currentPath, strlen($repeaterPath) + 1);
            $currentKey = explode('.', $relative)[0] ?? null;
        }

        $items = $repeater->getState();

        if (! is_array($items)) {
            return;
        }

        foreach (array_keys($items) as $itemKey) {
            $itemKey = (string) $itemKey;

            if ($currentKey !== null && $itemKey === $currentKey) {
                continue;
            }

            $set("{$repeaterPath}.{$itemKey}.{$toggleField}", false, isAbsolute: true);
        }
    }

    private static function findParentRepeater(Component $component): ?Repeater
    {
        $container = $component->getContainer();

        while ($container !== null) {
            $parent = $container->getParentComponent();

            if ($parent instanceof Repeater) {
                return $parent;
            }

            $container = $parent?->getContainer();
        }

        return null;
    }

    private static function isTruthy(mixed $state): bool
    {
        return $state === true || $state === 1 || $state === '1';
    }
}
