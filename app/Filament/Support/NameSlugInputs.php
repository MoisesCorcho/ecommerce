<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

/**
 * Shared name + slug form inputs for Filament Resources.
 *
 * The slug updates live from the name while it still matches the previous
 * auto-generated value; once the operator edits the slug, it stops overwriting.
 */
final class NameSlugInputs
{
    /**
     * @param  class-string<Model>  $modelClass
     * @return array{0: TextInput, 1: TextInput}
     */
    public static function make(
        string $modelClass,
        string $nameField = 'name',
        string $slugField = 'slug',
        ?string $nameLabel = null,
        ?string $slugLabel = null,
        ?string $namePlaceholder = null,
        ?string $slugPlaceholder = null,
        ?string $slugHelperText = null,
    ): array {
        $name = TextInput::make($nameField)
            ->label($nameLabel ?? __('filament_support.name_slug.name'))
            ->required()
            ->maxLength(255)
            ->live(debounce: 400)
            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) use ($slugField): void {
                $currentSlug = (string) ($get($slugField) ?? '');
                $previousAutoSlug = Str::slug((string) $old);

                // Keep syncing while empty or still equal to the auto-slug of the previous name.
                if ($currentSlug !== '' && $currentSlug !== $previousAutoSlug) {
                    return;
                }

                $set($slugField, filled($state) ? Str::slug($state) : '');
            });

        if ($namePlaceholder !== null) {
            $name->placeholder($namePlaceholder);
        }

        $slug = TextInput::make($slugField)
            ->label($slugLabel ?? __('filament_support.name_slug.slug'))
            ->maxLength(255)
            ->helperText($slugHelperText ?? __('filament_support.name_slug.slug_helper'))
            ->nullable()
            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? $state : null)
            ->unique(
                table: $modelClass,
                column: $slugField,
                ignoreRecord: true,
                modifyRuleUsing: fn (Unique $rule): Unique => $rule->whereNotNull($slugField),
            );

        if ($slugPlaceholder !== null) {
            $slug->placeholder($slugPlaceholder);
        }

        return [$name, $slug];
    }
}
