<?php

declare(strict_types=1);

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.storefront'), Title('Leen Handbags | Mis pedidos')] class extends Component
{
    use WithPagination;

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
