<?php

declare(strict_types=1);

return [
    'navigation' => [
        'group' => 'Marketing',
        'label' => 'Promotional Pop-ups',
    ],
    'model' => [
        'label' => 'Promotional Pop-up',
        'plural' => 'Promotional Pop-ups',
    ],
    'sections' => [
        'content' => 'Pop-up Content',
        'content_description' => 'Configure translatable texts, promotional image, and call-to-action button.',
        'coupon' => 'Coupon Linking',
        'coupon_description' => 'Attach a coupon code to boost conversions with quick copy functionality.',
        'schedule' => 'Behavior & Scheduling',
        'schedule_description' => 'Define opening delay, priority, and date range for storefront visibility.',
    ],
    'fields' => [
        'title' => 'Pop-up Title',
        'subtitle' => 'Subtitle / Description',
        'image' => 'Promotional Image',
        'image_helper' => 'Upload an attractive banner image (JPEG, PNG, WebP) for the modal.',
        'coupon' => 'Attached Coupon',
        'coupon_helper' => 'Optional. Select an active coupon to present inside the modal.',
        'cta_text' => 'Button Text (CTA)',
        'cta_url' => 'Destination Link (URL)',
        'cta_url_helper' => 'Optional. URL navigated to when clicking the main button.',
        'delay_seconds' => 'Display Delay (Seconds)',
        'delay_seconds_helper' => 'Seconds to wait after page load before displaying the popup (1 to 60s).',
        'is_active' => 'Active',
        'sort_order' => 'Sort Priority',
        'sort_order_helper' => 'Lower number indicates higher priority (e.g. 0 before 10).',
        'starts_at' => 'Starts At',
        'ends_at' => 'Ends At',
    ],
    'badges' => [
        'primary' => 'Primary',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'has_coupon' => 'With Coupon',
    ],
    'storefront' => [
        'eyebrow' => 'An offer sweeter than honey',
        'copy_code' => 'Copy code',
        'code_copied' => 'Copied!',
        'close' => 'Close popup',
        'off' => 'off',
    ],
];
