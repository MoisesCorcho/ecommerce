<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'Order',
        'plural' => 'Orders',
    ],

    'navigation' => [
        'label' => 'Orders',
    ],

    'fields' => [
        'order_number' => 'Order number',
        'email' => 'Email',
        'status' => 'Status',
        'currency' => 'Currency',
        'subtotal' => 'Subtotal',
        'shipping_cost' => 'Shipping',
        'discount' => 'Discount',
        'threshold_discount' => 'Volume discount (10%)',
        'coupon_code' => 'Coupon code',
        'tax_amount' => 'Tax',
        'total' => 'Total',
        'customer_notes' => 'Order notes',
        'first_name' => 'First name',
        'last_name' => 'Last name',
        'phone' => 'Phone',
        'shipping_full_name' => 'Full name',
        'shipping_phone' => 'Shipping phone',
        'shipping_address_line_1' => 'Address line 1',
        'shipping_address_line_2' => 'Address line 2',
        'shipping_city' => 'City',
        'shipping_state' => 'State / department',
        'shipping_country' => 'Country',
        'shipping_postal_code' => 'Postal code',
        'shipping_address_id' => 'Saved address',
        'created_at' => 'Created',
        'items' => 'Items',
        'product_name' => 'Product',
        'variant_label' => 'Variant',
        'sku' => 'SKU',
        'unit_price' => 'Unit price',
        'quantity' => 'Quantity',
    ],

    'sections' => [
        'summary' => 'Order summary',
        'customer' => 'Customer',
        'shipping' => 'Shipping address',
        'items' => 'Line items',
        'notes' => 'Notes',
    ],

    'actions' => [
        'confirm' => 'Place order',
        'cancel' => 'Cancel order',
        'use_saved_address' => 'Use saved address',
        'use_one_shot_address' => 'Use a different address',
    ],

    'shipping' => [
        'standard' => 'Standard shipping',
    ],

    'thank_you' => [
        'title' => 'Thank you for your order',
        'body' => 'Your order :number has been received and is pending payment.',
        'body_confirmed' => 'Your order :number has been received and confirmed.',
        'status' => 'Status: :status',
        'continue_shopping' => 'Continue shopping',
        'banner' => [
            'pending' => 'Your order is awaiting payment. Complete the payment to confirm your order.',
            'paid' => 'Payment confirmed. We are preparing your order.',
            'processing' => 'Your order is being prepared for shipment.',
            'shipped' => 'Your order is on its way.',
            'delivered' => 'Your order has been delivered.',
            'cancelled' => 'This order has been cancelled.',
            'refunded' => 'This order has been refunded.',
        ],
    ],

    'checkout' => [
        'title' => 'Checkout',
        'contact' => 'Contact',
        'shipping' => 'Shipping',
        'breadcrumb_home' => 'Home',
        'breadcrumb_cart' => 'Cart',
        'breadcrumb_checkout' => 'Checkout',
        'back_to_cart' => 'Back to cart',
        'summary_title' => 'Your order',
        'address_title' => 'Shipping address',
        'shipping_method' => 'Shipping method',
        'secure_badge' => 'Secure checkout',
        'secure_note' => 'Payment is processed securely after you confirm.',
        'stepper' => [
            'contact' => 'Contact',
            'address' => 'Address',
            'shipping' => 'Shipping',
            'payment' => 'Payment',
            'payment_hint' => 'Handled after you confirm',
        ],
        'placeholders' => [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '+1 555 000 0000',
            'address_line_1' => 'Street and number',
            'city' => 'City',
            'postal_code' => 'Postal code',
        ],
    ],

    'errors' => [
        'cart_empty' => 'Your cart is empty. Add products before checkout.',
        'cart_not_ready' => 'One or more cart items are not available. Return to the cart and review quantities or availability.',
        'insufficient_stock' => 'Not enough stock for ":product". Maximum available: :max.',
        'not_eligible' => '":product" is not available for purchase in the cart currency.',
        'access_denied' => 'You are not allowed to access this order.',
        'cart_access_denied' => 'You are not allowed to checkout this cart.',
        'cannot_cancel' => 'Only pending orders can be cancelled.',
        'cannot_cancel_payment_captured' => 'This order has a captured payment and cannot be cancelled. Resolve via refund or ops.',
        'invalid_address' => 'The selected address is invalid or does not belong to you.',
    ],

    'notifications' => [
        'cancelled' => 'Order cancelled.',
    ],
];
