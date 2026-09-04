<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Admin seed emails (Seeding / Provisioning only)
    |--------------------------------------------------------------------------
    |
    | Comma-separated list of emails used exclusively during database seeding
    | (php artisan db:seed / RoleAndAdminBackfillSeeder & OrderAndPaymentSeeder)
    | to provision the initial Spatie 'admin' role and link fixture orders.
    |
    | RUNTIME AUTHORIZATION NOTE:
    | This list is NOT evaluated at runtime. Filament panel access and policies
    | are governed 100% by Spatie RBAC via User::canAccessPanel() -> hasRole('admin').
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
    | Storefront currency preference (F14)
    |--------------------------------------------------------------------------
    |
    | The visitor's market currency drives the whole storefront, not just the
    | cart. It is resolved per request from session, then cookie, then the
    | country reported by the CDN, falling back to `default_currency`.
    |
    | `country_header` is the upstream header carrying an ISO 3166-1 alpha-2
    | country. Cloudflare sends CF-IPCountry. Leave the header absent and
    | detection is simply skipped — no error, no external lookup.
    |
    */

    'currency_preference' => [
        'cookie_name' => env('ECOMMERCE_CURRENCY_COOKIE', 'currency'),
        'cookie_lifetime' => (int) env('ECOMMERCE_CURRENCY_COOKIE_LIFETIME', 525600),
        'country_header' => env('ECOMMERCE_COUNTRY_HEADER', 'CF-IPCountry'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Trusted reverse proxies
    |--------------------------------------------------------------------------
    |
    | Behind a CDN the socket address is the CDN's, so the visitor's own address
    | only survives in forwarded headers. Those headers are attacker-controlled
    | on any request that did not come through the proxy, and several rate
    | limiters key on the visitor's address, so the list below is deliberately
    | an allowlist rather than a wildcard: the origin stays reachable on its
    | own address, and a wildcard would let anyone reaching it directly claim
    | any address they like.
    |
    | Defaults to Cloudflare's published ranges (cloudflare.com/ips, fetched
    | 2026-08-19). Set ECOMMERCE_TRUSTED_PROXIES to a comma-separated list to
    | override, or to an empty string to trust none.
    |
    */

    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ECOMMERCE_TRUSTED_PROXIES', implode(',', [
            // IPv4
            '173.245.48.0/20',
            '103.21.244.0/22',
            '103.22.200.0/22',
            '103.31.4.0/22',
            '141.101.64.0/18',
            '108.162.192.0/18',
            '190.93.240.0/20',
            '188.114.96.0/20',
            '197.234.240.0/22',
            '198.41.128.0/17',
            '162.158.0.0/15',
            '104.16.0.0/13',
            '104.24.0.0/14',
            '172.64.0.0/13',
            '131.0.72.0/22',
            // IPv6
            '2400:cb00::/32',
            '2606:4700::/32',
            '2803:f800::/32',
            '2405:b500::/32',
            '2405:8100::/32',
            '2a06:98c0::/29',
            '2c0f:f248::/32',
        ]))),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Storefront locale (F13)
    |--------------------------------------------------------------------------
    |
    | Supported languages live in App\Enums\Localization\LocaleEnum (es | en).
    | The visitor's choice is kept in the session for the current visit and
    | mirrored to a long-lived functional cookie so a returning guest is not
    | dropped back to APP_LOCALE once the session expires.
    |
    */

    'locale' => [
        'cookie_name' => env('ECOMMERCE_LOCALE_COOKIE', 'locale'),
        'cookie_lifetime' => (int) env('ECOMMERCE_LOCALE_COOKIE_LIFETIME', 525600),
    ],

    /*
    |--------------------------------------------------------------------------
    | Standard shipping (F04)
    |--------------------------------------------------------------------------
    |
    | Single shipping option "Envío estándar". Costs are stored as integers
    | in currency minor units: COP pesos (1 = $1 COP), EUR/USD cents (500 = €5.00 / $5.00).
    | A cost of 0 enables free shipping for that market currency.
    |
    */

    'shipping' => [
        'standard_cost_cop' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_COP', 0),
        'standard_cost_eur' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_EUR', 0),
        'standard_cost_usd' => (int) env('ECOMMERCE_SHIPPING_STANDARD_COST_USD', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payments (F05)
    |--------------------------------------------------------------------------
    |
    | Hosted checkout providers and payment limits. Keys never committed; use env only.
    | Provider routing: COP → Bold, EUR/USD → Stripe (see CurrencyEnum::paymentProvider()).
    |
    | - min_chargeable_amounts: Minimum transaction total allowed by each gateway's
    |   API (minor units: COP 1.000 pesos, EUR 50 cents, USD 50 cents). Amounts
    |   below these limits cannot be processed and are absorbed during checkout.
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
        'min_chargeable_amounts' => [
            'COP' => (int) env('PAYMENTS_MIN_AMOUNT_COP', 1_000),
            'EUR' => (int) env('PAYMENTS_MIN_AMOUNT_EUR', 50),
            'USD' => (int) env('PAYMENTS_MIN_AMOUNT_USD', 50),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact page (contacto)
    |--------------------------------------------------------------------------
    |
    | Contact channels and metadata for visitor inquiries.
    |
    | - inbox: Receives every contact-form submission (Mail::to).
    | - public_email: Displayed in UI and used as fallback in error banners.
    | - phone: Human-formatted phone string for presentation (e.g. '+57 300 123 4567').
    | - phone_raw: E.164 normalized phone string without spaces for 'tel:' links.
    | - whatsapp_url: Pre-encoded URL for direct WhatsApp web/app redirection.
    |
    */

    'contact' => [
        'inbox' => env('CONTACT_MAIL_TO', env('CONTACT_PUBLIC_EMAIL', 'leenhandbags@gmail.com')),
        'public_email' => env('CONTACT_PUBLIC_EMAIL', 'leenhandbags@gmail.com'),
        'phone' => env('CONTACT_PHONE', '+57 300 123 4567'),
        'phone_raw' => env('CONTACT_PHONE_RAW', '+573001234567'),
        'whatsapp' => env('CONTACT_WHATSAPP', '+57 300 123 4567'),
        'whatsapp_url' => env('CONTACT_WHATSAPP_URL', 'https://wa.me/573001234567'),
        'social' => [
            'instagram' => env('SOCIAL_INSTAGRAM_URL', 'https://www.instagram.com/leen_____________________/'),
            'tiktok' => env('SOCIAL_TIKTOK_URL', 'https://www.tiktok.com/@leenhandbags'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cart threshold discount (F17)
    |--------------------------------------------------------------------------
    |
    | Automatic progressive discount applied when cart subtotal meets or exceeds
    | the currency threshold. Amounts are in minor units (COP pesos, EUR/USD cents).
    |
    */

    'cart_threshold_discount' => [
        'enabled' => (bool) env('ECOMMERCE_THRESHOLD_DISCOUNT_ENABLED', true),
        'percentage' => (int) env('ECOMMERCE_THRESHOLD_DISCOUNT_PERCENTAGE', 10),
        'min_amounts' => [
            'COP' => (int) env('ECOMMERCE_THRESHOLD_MIN_COP', 1_200_000),
            'EUR' => (int) env('ECOMMERCE_THRESHOLD_MIN_EUR', 30_000),
            'USD' => (int) env('ECOMMERCE_THRESHOLD_MIN_USD', 32_000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Wishlist automated marketing alerts (F18)
    |--------------------------------------------------------------------------
    |
    | Configuration for scheduled price-drop and low-stock marketing alerts.
    |
    | - low_stock_threshold: Triggers alert when 1 <= stock <= threshold.
    | - price_drop_cooldown_days: Min days between price drop emails for same product.
    | - low_stock_cooldown_days: Min days between low stock emails for same product.
    | - max_alerts_per_user: Anti-flood limit per scheduled execution (10:00 AM).
    |
    */

    'wishlist_alerts' => [
        'enabled' => (bool) env('ECOMMERCE_WISHLIST_ALERTS_ENABLED', true),
        'low_stock_threshold' => (int) env('ECOMMERCE_WISHLIST_LOW_STOCK_THRESHOLD', 3),
        'price_drop_cooldown_days' => (int) env('ECOMMERCE_WISHLIST_PRICE_DROP_COOLDOWN_DAYS', 2),
        'low_stock_cooldown_days' => (int) env('ECOMMERCE_WISHLIST_LOW_STOCK_COOLDOWN_DAYS', 7),
        'max_alerts_per_user' => (int) env('ECOMMERCE_WISHLIST_MAX_ALERTS_PER_USER', 3),
    ],

];
