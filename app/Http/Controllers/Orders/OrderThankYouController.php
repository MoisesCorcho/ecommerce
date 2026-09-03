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

        $hasValidSignature = URL::hasValidSignature($request, absolute: true, ignoreQuery: [
            'bold-order-id',
            'bold_order_id',
            'payment_id',
            'transaction_id',
            'reference',
            'status',
            'utm_source',
            'utm_medium',
            'utm_campaign',
        ]);
        $user = $request->user();

        $allowed = $hasValidSignature
            || ($user !== null && $user->can('view', $order));

        abort_unless($allowed, 403, __('orders.errors.access_denied'));

        $paymentReturn = $request->query('payment');
        $paymentReturn = is_string($paymentReturn) ? $paymentReturn : null;

        $payUrl = null;
        if ($order->status === OrderStatusEnum::Pending && (int) $order->total >= $order->currency->minimumChargeableAmount()) {
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
