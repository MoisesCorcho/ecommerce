<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] class extends Component
{
    use WithPagination;

    #[Url]
    public ?string $category = null;

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?string $slug = null): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function render()
    {
        return $this->view();
    }

    public function with(): array
    {
        $categories = PostCategory::query()
            ->active()
            ->ordered()
            ->get();

        $postsQuery = Post::query()
            ->published()
            ->with(['category', 'author'])
            ->latest('published_at');

        if (filled($this->category)) {
            $postsQuery->whereHas('category', function (Builder $query): void {
                $query->where('slug', $this->category);
            });
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        return [
            'categories' => $categories,
            'posts' => $posts,
            'activeCategory' => $this->category,
        ];
    }
};
