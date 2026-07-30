<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCategoryAction
{
    /**
     * @param  array{name: string, slug?: string|null, image_path?: string|null, parent_id?: int|null, sort_order?: int|null}  $data
     */
    public function __invoke(array $data): Category
    {
        $name = trim((string) $data['name']);
        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => __('categories.validation.name_required'),
            ]);
        }

        $slug = $this->resolveSlug($data['slug'] ?? null, $name);

        $sortOrder = array_key_exists('sort_order', $data) && $data['sort_order'] !== null
            ? (int) $data['sort_order']
            : ((int) Category::query()->max('sort_order')) + 1;

        return Category::query()->create([
            'name' => $name,
            'slug' => $slug,
            'image_path' => $data['image_path'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $sortOrder,
        ]);
    }

    private function resolveSlug(?string $slug, string $name): string
    {
        $base = filled($slug) ? Str::slug($slug) : Str::slug($name);
        if ($base === '') {
            $base = 'categoria';
        }

        $candidate = $base;
        $suffix = 1;
        while (Category::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
