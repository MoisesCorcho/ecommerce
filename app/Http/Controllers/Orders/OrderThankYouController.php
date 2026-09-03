<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
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

        $paymentReturn = $request->query('payment');
        $paymentReturn = is_string($paymentReturn) ? $paymentReturn : null;

        $payUrl = null;
        if ($order->status === OrderStatusEnum::Pending && (int) $order->total > 0) {
            if ($hasValidSignature) {
                $payUrl = URL::temporarySignedRoute(
                    'orders.pay',
                    now()->addDay(),
                    ['order' => $order->id],
                );
            } elseif ($user !== null && $user->can('pay', $order)) {
                $payUrl = route('orders.pay', $order);
            }
        }

        return view('orders.thank-you', [
            'order' => $order,
            'paymentReturn' => $paymentReturn,
            'payUrl' => $payUrl,
        ]);
    }
}
