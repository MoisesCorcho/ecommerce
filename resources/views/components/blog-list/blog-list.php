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

    #[Url]
    public string $search = '';

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?string $slug = null): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->category = null;
        $this->search = '';
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

        if (filled($this->search)) {
            $variants = $this->getSearchVariants($this->search);

            $postsQuery->where(function (Builder $query) use ($variants): void {
                foreach ($variants as $term) {
                    $query->orWhereRaw('LOWER(title) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(excerpt) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(content) LIKE ?', ["%{$term}%"]);
                }
            });
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        return [
            'categories' => $categories,
            'posts' => $posts,
            'activeCategory' => $this->category,
            'search' => $this->search,
        ];
    }

    /**
     * Generate search variants for accent-insensitive matching (e.g. guia <-> guía).
     *
     * @return array<int, string>
     */
    protected function getSearchVariants(string $term): array
    {
        $term = mb_strtolower(trim($term));
        $unaccented = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'u'],
            $term
        );

        $variants = [$term, $unaccented];

        $accentsMap = ['a' => 'á', 'e' => 'é', 'i' => 'í', 'o' => 'ó', 'u' => 'ú'];
        $len = mb_strlen($unaccented);

        if ($len <= 50) {
            for ($i = 0; $i < $len; $i++) {
                $char = mb_substr($unaccented, $i, 1);
                if (isset($accentsMap[$char])) {
                    $variants[] = mb_substr($unaccented, 0, $i).$accentsMap[$char].mb_substr($unaccented, $i + 1);
                }
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }
};
