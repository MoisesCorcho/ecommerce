<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderThankYouController extends Controller
{
    public function __invoke(Request $request, Order $order): View
    {
        $order->loadMissing('items');

        $hasValidSignature = $request->hasValidSignature();
        $user = $request->user();

        $allowed = $hasValidSignature
            || ($user !== null && $user->can('view', $order));

        abort_unless($allowed, 403, __('orders.errors.access_denied'));

        return view('orders.thank-you', [
            'order' => $order,
            'title' => __('orders.thank_you.title'),
        ]);
    }
}
