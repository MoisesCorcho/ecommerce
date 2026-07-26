<?php

declare(strict_types=1);

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront')] class extends Component
{
    use WithPagination;

    public function render()
    {
        return $this->view()->title('Leen Handbags | '.__('account.orders.title'));
    }

    public function with(): array
    {
        return [
            'orders' => Order::query()
                ->visibleInAccountHistory((int) Auth::id())
                ->latest()
                ->paginate(10),
        ];
    }
};
