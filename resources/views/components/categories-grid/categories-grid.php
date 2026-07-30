<?php

use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

new class extends Component
{
    public function with(): array
    {
        return [
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'hasImageColumn' => Schema::hasColumn('categories', 'image_path'),
        ];
    }
};
