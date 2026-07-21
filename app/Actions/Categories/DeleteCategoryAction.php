<?php

declare(strict_types=1);

namespace App\Actions\Categories;

use App\Models\Category;

class DeleteCategoryAction
{
    public function __invoke(Category $category): void
    {
        $category->delete();
    }
}
