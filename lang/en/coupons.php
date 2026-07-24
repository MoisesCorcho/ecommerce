<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'coupon',
        'plural' => 'coupons',
    ],

    'navigation' => [
        'label' => 'Coupons',
    ],

    'pages' => [
        'create_title' => 'New coupon',
        'edit_title' => 'Edit coupon',
    ],

    'sections' => [
        'identity' => 'Coupon identity',
        'identity_description' => 'Unique code customers enter at checkout. Codes are stored uppercase.',
        'discount' => 'Discount rules',
        'discount_description' => 'Percentage (1–100, any currency) or fixed amount in one currency. Discount applies to line subtotal only.',
        'limits' => 'Usage limits and validity',
        'limits_description' => 'Optional caps and window. Empty means unlimited or open-ended.',
        'status' => 'Status',
        'redemptions' => 'Redemptions',
    ],

    'fields' => [
        'code' => 'Code',
        'type' => 'Type',
        'value' => 'Value',
        'currency' => 'Currency',
        'min_order_amount' => 'Minimum order amount',
        'min_order_currency' => 'Minimum order currency',
        'usage_limit' => 'Global usage limit',
        'usage_limit_per_user' => 'Per-user usage limit',
        'used_count' => 'Times used',
        'starts_at' => 'Starts at',
        'expires_at' => 'Expires at',
        'is_active' => 'Active',
        'status' => 'Status',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
        'discount_amount' => 'Discount amount',
        'order_number' => 'Order',
        'user' => 'User',
        'redeemed_at' => 'Redeemed at',
    ],

    'placeholders' => [
        'code' => 'SUMMER25',
        'value_percentage' => '10',
        'value_fixed' => '50000',
        'unlimited' => 'Unlimited',
        'filter_all' => 'All',
        'no_user' => 'Guest',
    ],

    'helpers' => [
        'code' => 'Letters, numbers and hyphens. Stored uppercase (max 32).',
        'value_percentage' => 'Integer from 1 to 100. Floor applied when calculating.',
        'value_fixed' => 'Integer minor units (COP pesos / EUR cents).',
        'currency_fixed' => 'Required for fixed coupons. Must match the cart currency.',
        'currency_percentage' => 'Leave empty for percentage coupons (applies to COP and EUR).',
        'min_order' => 'Optional. Compared against line subtotal in the cart currency.',
        'usage_limit' => 'Null = unlimited redemptions.',
        'usage_limit_per_user' => 'Applies only to authenticated users. Guests use the global limit only.',
        'is_active' => 'Inactive coupons cannot be applied even if dates are valid.',
        'immutable_after_redemption' => 'Type, value and currency cannot change once the coupon has redemptions.',
    ],

    'filters' => [
        'type' => 'Type',
        'active' => 'Active',
        'active_only' => 'Active only',
        'inactive_only' => 'Inactive only',
        'currency' => 'Currency',
    ],

    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],

    'actions' => [
        'create' => 'New coupon',
        'edit' => 'Edit',
        'confirm_delete' => 'Delete',
    ],

    'empty' => [
        'heading' => 'No coupons yet',
        'description' => 'Create a percentage or fixed coupon for checkout promotions.',
        'redemptions_heading' => 'No redemptions yet',
        'redemptions_description' => 'Redemptions appear when orders are created with this code.',
    ],

    'notifications' => [
        'created' => 'Coupon created.',
        'updated' => 'Coupon updated.',
    ],

    'validation' => [
        'code_required' => 'The coupon code is required.',
        'code_format' => 'The coupon code may only contain letters, numbers and hyphens (max 32).',
        'code_unique' => 'This coupon code is already in use.',
        'type_required' => 'The coupon type is required.',
        'value_required' => 'The coupon value is required.',
        'value_percentage_range' => 'Percentage value must be an integer between 1 and 100.',
        'value_fixed_positive' => 'Fixed value must be a positive integer.',
        'currency_required_for_fixed' => 'Currency is required for fixed coupons.',
        'currency_must_be_null_for_percentage' => 'Percentage coupons must not set a currency.',
        'min_order_currency_required' => 'Minimum order currency is required when a minimum amount is set.',
        'immutable_fields' => 'Type, value and currency cannot be changed after the coupon has been redeemed.',
    ],

    'errors' => [
        // Storefront-safe generic message (D45 / R14).
        'invalid' => 'This coupon code is not valid.',
        // Admin / domain-specific reasons (also usable in logs).
        'not_found' => 'Coupon code not found.',
        'inactive' => 'This coupon is inactive.',
        'not_started' => 'This coupon is not valid yet.',
        'expired' => 'This coupon has expired.',
        'currency_mismatch' => 'This coupon does not apply to the cart currency.',
        'min_not_met' => 'The order subtotal does not meet the coupon minimum.',
        'usage_exhausted' => 'This coupon has reached its usage limit.',
        'per_user_exhausted' => 'You have already used this coupon the maximum number of times.',
        'immutable_fields' => 'Type, value and currency cannot be changed after redemptions exist.',
        'rate_limited' => 'Too many coupon attempts. Please wait a moment and try again.',
    ],

    'relation' => [
        'redemptions_title' => 'Redemptions',
    ],
];
