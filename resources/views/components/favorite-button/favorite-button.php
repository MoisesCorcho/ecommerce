<?php

use App\Actions\Wishlist\ToggleWishlistAction;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $productVariantId;

    public bool $isFavorited = false;

    public function mount(int $productVariantId): void
    {
        $this->productVariantId = $productVariantId;
        $this->isFavorited = Auth::check()
            && Wishlist::where('user_id', Auth::id())->where('product_variant_id', $productVariantId)->exists();
    }

    public function toggle(ToggleWishlistAction $toggleWishlist): void
    {
        if (Auth::guest()) {
            $this->redirect(route('login'));

            return;
        }

        $this->isFavorited = $toggleWishlist(Auth::user(), ProductVariant::findOrFail($this->productVariantId));

        $this->dispatch('toast', message: $this->isFavorited
            ? __('storefront.products.added_to_favorites')
            : __('storefront.products.removed_from_favorites'));
    }
};
