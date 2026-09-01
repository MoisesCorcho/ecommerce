<?php

declare(strict_types=1);

use App\Enums\Blog\PostStatusEnum;
use App\Models\Post;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public Post $post;

    public Collection $relatedPosts;

    public bool $isPreview = false;

    public function mount(string $slug): void
    {
        /** @var Post $post */
        $post = Post::query()
            ->where('slug', $slug)
            ->with(['category', 'author'])
            ->firstOrFail();

        $isPublished = ($post->status === PostStatusEnum::Published && $post->published_at !== null && $post->published_at->isPast());

        if (! $isPublished) {
            $isAdmin = false;
            if (auth()->check()) {
                $user = auth()->user();
                $adminEmails = (array) config('ecommerce.admin_emails', []);
                $isAdmin = in_array($user->email, $adminEmails, true) || (method_exists($user, 'hasRole') && $user->hasRole('admin'));
            }

            if (! $isAdmin) {
                abort(404);
            }

            $this->isPreview = true;
        }

        $this->post = $post;
        $this->relatedPosts = $this->resolveRelatedPosts($post);
    }

    public function render()
    {
        return $this->view();
    }

    private function resolveRelatedPosts(Post $post): Collection
    {
        $limit = 3;
        $related = collect();

        if ($post->post_category_id) {
            $related = Post::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->where('post_category_id', $post->post_category_id)
                ->with(['category', 'author'])
                ->latest('published_at')
                ->take($limit)
                ->get();
        }

        if ($related->count() < $limit) {
            $needed = $limit - $related->count();
            $excludeIds = $related->pluck('id')->push($post->id)->all();

            $fallbacks = Post::query()
                ->published()
                ->whereNotIn('id', $excludeIds)
                ->with(['category', 'author'])
                ->latest('published_at')
                ->take($needed)
                ->get();

            $related = $related->concat($fallbacks);
        }

        return $related;
    }
};
