<?php

declare(strict_types=1);

return [
    'notification_types' => [
        'price_drop' => 'Price drop',
        'low_stock' => 'Low stock',
    ],
    'mail' => [
        'price_drop_subject' => 'Good news! :product is now on sale',
        'price_drop_heading' => 'Your favorite handbag has a special price!',
        'price_drop_subheading' => 'An item from your wishlist has dropped in price.',
        'price_drop_body' => 'You saved this handbag to your wishlist and we wanted to let you know right away that its price has dropped.',
        'price_drop_cta' => 'Shop now in store',
        'old_price_label' => 'Was',
        'new_price_label' => 'Now',
        'low_stock_subject' => 'Only a few left of :product!',
        'low_stock_heading' => 'Hurry, almost sold out!',
        'low_stock_subheading' => 'An item on your wishlist is running low on stock.',
        'low_stock_body' => 'There are only :stock units left of your favorite handbag. Grab yours before it\'s gone!',
        'low_stock_badge' => 'Only :stock left',
        'low_stock_cta' => 'Buy now before it sells out',
        'footer_note' => 'You are receiving this email because you saved this product to your wishlist at Leen.',
    ],
];
