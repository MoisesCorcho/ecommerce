<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin panel emails
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of emails allowed into the Filament admin panel.
    | Compared strictly to the authenticated user's email (F01 gate; no Spatie).
    |
    */

    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ADMIN_EMAILS', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Default storefront currency
    |--------------------------------------------------------------------------
    |
    | ISO code aligned with App\Enums\Commerce\CurrencyEnum (COP | EUR).
    | Used for public catalog listing and product detail prices.
    |
    */

    'default_currency' => env('ECOMMERCE_DEFAULT_CURRENCY', 'COP'),

    /*
    |--------------------------------------------------------------------------
    | Standard shipping (F04)
    |--------------------------------------------------------------------------
    |
    | Single shipping option "Envío estándar". Costs are integers in the same
    | minor-unit convention as catalog prices (COP pesos, EUR cents).
    |
    */

    'shipping' => [
        'standard_cost_cop' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_COP', 0),
        'standard_cost_eur' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_EUR', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payments (F05)
    |--------------------------------------------------------------------------
    |
    | Hosted checkout providers. Keys never committed; use env only.
    | COP → Bold, EUR → Stripe (see CurrencyEnum::paymentProvider()).
    |
    */

    'payments' => [
        'stripe' => [
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'api_base' => env('STRIPE_API_BASE', 'https://api.stripe.com'),
        ],
        'bold' => [
            'api_key' => env('BOLD_API_KEY'),
            'secret_key' => env('BOLD_SECRET_KEY'),
            // Explicit value wins (including empty string for Bold test mode).
            // Leave unset to fall back to secret_key in production.
            'webhook_secret' => env('BOLD_WEBHOOK_SECRET'),
            'api_base' => env('BOLD_API_BASE', 'https://integrations.api.bold.co'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact page (contacto)
    |--------------------------------------------------------------------------
    |
    | `inbox` receives every contact-form submission (Mail::to). `public_email`
    | is shown to visitors as the info-column mailto and as the error-banner
    | fallback when the transactional send fails.
    |
    */

    'contact' => [
        'inbox' => env('CONTACT_MAIL_TO'),
        'public_email' => env('CONTACT_PUBLIC_EMAIL'),
    ],

];
