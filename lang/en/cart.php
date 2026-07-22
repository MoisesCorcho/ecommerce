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
];
