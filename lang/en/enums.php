<?php

declare(strict_types=1);

return [
    'currency' => [
        'COP' => 'Colombian peso',
        'EUR' => 'Euro',
        'USD' => 'US dollar',
    ],

    'order_status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'payment_status' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'declined' => 'Declined',
        'refunded' => 'Refunded',
    ],

    'payment_provider' => [
        'stripe' => 'Stripe',
        'bold' => 'Bold',
    ],

    'coupon_type' => [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed amount',
    ],

    'contact_submission_status' => [
        'new' => 'New',
        'read' => 'Read',
        'replied' => 'Replied',
        'archived' => 'Archived',
    ],

    'size' => [
        'mini' => 'Mini',
        'medium' => 'Medium',
        'maxi' => 'Maxi',
        'one_size' => 'One Size',
    ],

    'post_status' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],
];
