<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UpdateCategoryAction
{
    /**
     * @param  array{name?: string, slug?: string|null, parent_id?: int|null, sort_order?: int|null}  $data
     */
    public function __invoke(Category $category, array $data): Category
    {
        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw ValidationException::withMessages([
                    'name' => 'El nombre de la categoría es obligatorio.',
                ]);
            }
            $category->name = $name;
        }

        if (array_key_exists('slug', $data)) {
            $slugInput = $data['slug'];
            $slug = filled($slugInput)
                ? Str::slug((string) $slugInput)
                : Str::slug($category->name);

            if ($slug === '') {
                $slug = 'categoria';
            }

            $exists = Category::query()
                ->where('slug', $slug)
                ->whereKeyNot($category->getKey())
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'slug' => 'El slug ya está en uso por otra categoría.',
                ]);
            }

            $category->slug = $slug;
        }

        if (array_key_exists('parent_id', $data)) {
            $category->parent_id = $data['parent_id'];
        }

        if (array_key_exists('sort_order', $data)) {
            $category->sort_order = (int) $data['sort_order'];
        }

        $category->save();

        return $category->refresh();
    }
}
