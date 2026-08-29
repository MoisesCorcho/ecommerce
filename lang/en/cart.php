<?php

declare(strict_types=1);

return [
    'errors' => [
        'not_eligible' => 'This product variant is not available for purchase in the cart currency.',
        'insufficient_stock' => 'Not enough stock for this variant. Maximum available: :max.',
        'quantity_max' => 'Quantity cannot exceed :max units per line.',
        'quantity_invalid' => 'Quantity must be zero (to remove) or a positive integer.',
        'currency_blocked' => 'Cannot change currency: one or more cart lines have no price in :currency.',
        'access_denied' => 'You are not allowed to modify this cart.',
        'item_not_found' => 'The cart line was not found.',
        'variant_not_found' => 'The product variant was not found.',
    ],

    'fields' => [
        'quantity' => 'Quantity',
        'currency' => 'Currency',
        'product_variant_id' => 'Product variant',
    ],

    'page' => [
        'title' => 'Cart',
        'breadcrumb_home' => 'Home',
        'breadcrumb_cart' => 'Cart',
        'currency_label' => 'Currency',
        'clear_cart' => 'Clear cart',
        'clear_cart_confirm' => 'Are you sure? This will remove all items from your cart.',
        'clear_cart_cancel' => 'Cancel',
    ],

    'line' => [
        'sku_label' => 'REF',
        'decrease_quantity' => 'Decrease quantity',
        'increase_quantity' => 'Increase quantity',
        'remove' => 'Remove item',
        'out_of_stock' => 'Out of stock',
        'unit_price_suffix' => 'each',
    ],

    'summary' => [
        'title' => 'Order summary',
        'items_count' => ':count item|:count items',
        'subtotal' => 'Subtotal',
        'threshold_discount' => 'Volume discount (10%)',
        'total' => 'Total',
        'checkout' => 'Checkout',
        'continue_shopping' => 'Continue shopping',
    ],

    'threshold' => [
        'progress' => 'Add :amount more to get 10% off',
        'unlocked' => 'You unlocked 10% discount!',
        'discount_label' => 'Volume discount (10%)',
    ],

    'empty' => [
        'title' => 'Your cart is empty',
        'message' => 'Explore our collection and find your next favorite piece.',
        'cta' => 'Explore products',
    ],

    'status' => [
        'quantity_updated' => 'Quantity updated.',
        'line_removed' => 'Item removed.',
        'cart_cleared' => 'Cart cleared.',
        'currency_updated' => 'Currency updated.',
    ],

    'stock_banner' => [
        'message' => 'The availability of one or more items in your cart has changed.',
    ],
];
