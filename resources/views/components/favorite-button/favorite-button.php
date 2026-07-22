<?php

use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $productId;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }
};
