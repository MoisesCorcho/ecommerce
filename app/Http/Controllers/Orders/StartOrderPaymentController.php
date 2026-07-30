<?php

declare(strict_types=1);

namespace App\Http\Controllers\Orders;

use App\Actions\Payments\StartOrderPaymentAction;
use App\Exceptions\Payments\OrderNotPayableException;
use App\Exceptions\Payments\PaymentGatewayException;
use App\Exceptions\Payments\PaymentStockUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Throwable;

class StartOrderPaymentController extends Controller
{
    public function __invoke(
        Request $request,
        Order $order,
        StartOrderPaymentAction $startOrderPayment,
    ): RedirectResponse {
        $user = $request->user();
        $allowed = $request->hasValidSignature()
            || ($user !== null && $user->can('pay', $order));

        abort_unless($allowed, 403, __('payments.errors.access_denied'));

        try {
            $result = $startOrderPayment((int) $order->id);

            return redirect()->away($result->redirectUrl);
        } catch (OrderNotPayableException|PaymentStockUnavailableException|PaymentGatewayException $e) {
            return redirect()
                ->to($this->thankYouUrl($request, $order))
                ->with('payment_error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->to($this->thankYouUrl($request, $order))
                ->with('payment_error', __('payments.errors.gateway'));
        }
    }

    private function thankYouUrl(Request $request, Order $order): string
    {
        if ($request->hasValidSignature()) {
            return URL::temporarySignedRoute(
                'orders.thank-you',
                now()->addDay(),
                ['order' => $order->id],
            );
        }

        return route('orders.thank-you', $order);
    }
}
