<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;
use Illuminate\Support\Facades\Storage;

class DeleteCategoryAction
{
    public function __invoke(Category $category): void
    {
        if ($category->image_path && Storage::disk('public')->exists($category->image_path)) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();
    }
}
