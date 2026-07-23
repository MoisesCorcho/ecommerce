<?php

declare(strict_types=1);

return [
    'actions' => [
        'pay' => 'Pay now',
        'retry' => 'Retry payment',
    ],

    'fields' => [
        'provider' => 'Provider',
        'status' => 'Payment status',
        'amount' => 'Amount',
        'currency' => 'Currency',
        'external_id' => 'External ID',
        'payment_method' => 'Payment method',
        'paid_at' => 'Paid at',
        'refunded_at' => 'Refunded at',
        'created_at' => 'Created',
    ],

    'sections' => [
        'payments' => 'Payment attempts',
    ],

    'return' => [
        'processing' => 'We are confirming your payment. This page will reflect the final status once the provider confirms it.',
        'cancelled' => 'Payment was not completed. You can try again while the order is still pending.',
        'paid' => 'Payment confirmed. Thank you!',
    ],

    'errors' => [
        'not_payable' => 'This order cannot be paid in its current status.',
        'already_paid' => 'This order is already paid.',
        'stock_unavailable' => 'One or more items no longer have enough stock to complete payment.',
        'access_denied' => 'You are not allowed to pay for this order.',
        'gateway' => 'The payment provider could not start the checkout session. Please try again.',
        'invalid_webhook_signature' => 'Invalid payment webhook signature.',
        'payment_not_found' => 'No payment attempt matches this webhook event.',
        'stock_conflict' => 'Payment was approved but the order could not be marked paid due to insufficient stock. Operations has been notified.',
    ],
];
