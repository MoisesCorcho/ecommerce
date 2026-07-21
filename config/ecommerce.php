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

];
