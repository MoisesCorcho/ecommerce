<?php

declare(strict_types=1);

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.storefront')] class extends Component
{
    public function render()
    {
        return $this->view();
    }
};
