<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileOrderDetailController extends Controller
{
    public function __invoke(Request $request, Order $order): View
    {
        abort_unless($request->user()->can('view', $order), 403, __('orders.errors.access_denied'));

        $order->loadMissing('items');

        return view('account.orders.show', [
            'order' => $order,
            'title' => __('account.orders.detail_title'),
        ]);
    }
}
