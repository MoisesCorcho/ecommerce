<?php

declare(strict_types=1);

return [

    'breadcrumb' => [
        'home' => 'Home',
        'faq' => 'FAQ',
    ],

    'title' => 'Frequently Asked Questions',
    'subtitle' => 'Find quick answers to common questions about our products, orders, and services.',

    'categories' => [

        'compras' => [
            'label' => 'Shopping',
            'questions' => [
                [
                    'q' => 'How do I place an order?',
                    'a' => 'Browse our catalog, select your desired bag and color, add it to your cart, and proceed to checkout. You can complete your purchase as a guest or with a registered account.',
                ],
                [
                    'q' => 'Can I modify or cancel my order after placing it?',
                    'a' => 'If your order has not yet been processed, please contact us as soon as possible and we will do our best to assist you. Once an order enters the shipping process, it can no longer be modified.',
                ],
                [
                    'q' => 'Are the products shown exactly what I will receive?',
                    'a' => 'We make every effort to display colors and details as accurately as possible. Slight variations may occur due to screen settings, but the quality and craftsmanship you see are what you will receive.',
                ],
                [
                    'q' => 'Do you offer gift wrapping?',
                    'a' => 'At this time we do not offer gift wrapping. Each piece is carefully packaged to ensure it arrives in perfect condition.',
                ],
            ],
        ],

        'envios' => [
            'label' => 'Shipping',
            'questions' => [
                [
                    'q' => 'What are the shipping options and delivery times?',
                    'a' => 'We offer standard shipping. Delivery times vary by destination: typically 3–5 business days within Colombia and 7–14 business days to Europe. You will receive a confirmation email with tracking details once your order ships.',
                ],
                [
                    'q' => 'Do you offer free shipping?',
                    'a' => 'Shipping costs are calculated at checkout based on your location and the selected shipping method. We occasionally run promotions that include free shipping — subscribe to our newsletter to stay informed.',
                ],
                [
                    'q' => 'Can I track my order?',
                    'a' => 'Yes. Once your order has been shipped, you will receive an email with a tracking number and a link to follow your package in real time.',
                ],
                [
                    'q' => 'Do you ship internationally?',
                    'a' => 'Yes, we ship to Colombia and select European countries. If your destination is not available at checkout, please contact us and we will explore shipping options for you.',
                ],
            ],
        ],

        'pagos' => [
            'label' => 'Payments',
            'questions' => [
                [
                    'q' => 'What payment methods do you accept?',
                    'a' => 'We accept payments through Bold (for Colombian peso transactions) and Stripe (for euro transactions). Both platforms support major credit and debit cards.',
                ],
                [
                    'q' => 'Is it safe to pay on your website?',
                    'a' => 'Absolutely. All payments are processed through secure, certified payment gateways. We never store your card details on our servers.',
                ],
                [
                    'q' => 'Can I pay in a currency different from my country?',
                    'a' => 'Our store supports COP (Colombian peso) and EUR (euro). The available currency is determined by your browsing context. Prices are always displayed in the currency that will be charged.',
                ],
                [
                    'q' => 'My payment was declined. What should I do?',
                    'a' => 'Please verify that your card details are correct and that your bank authorizes online purchases. If the issue persists, try a different payment method or contact your bank directly.',
                ],
            ],
        ],

        'cambios' => [
            'label' => 'Returns & Exchanges',
            'questions' => [
                [
                    'q' => 'What is your return policy?',
                    'a' => 'You may request a return within 14 days of receiving your order, provided the item is in its original condition, unused, and with all tags attached. Please contact us to initiate the process.',
                ],
                [
                    'q' => 'How do I request an exchange?',
                    'a' => 'Contact us with your order number and the details of the item you wish to exchange. We will guide you through the process and confirm availability of the replacement.',
                ],
                [
                    'q' => 'Who covers the cost of return shipping?',
                    'a' => 'Return shipping costs are the responsibility of the customer, unless the item arrived damaged or defective. In such cases, we will cover the return shipping cost.',
                ],
                [
                    'q' => 'How long does it take to receive my refund?',
                    'a' => 'Once we receive and inspect the returned item, refunds are processed within 5–10 business days. The refund will be issued to the original payment method.',
                ],
            ],
        ],

        'cuenta' => [
            'label' => 'My Account',
            'questions' => [
                [
                    'q' => 'Do I need an account to place an order?',
                    'a' => 'No, you can check out as a guest. However, creating an account allows you to track your orders, manage your addresses, save items to your wishlist, and enjoy a faster checkout experience.',
                ],
                [
                    'q' => 'How do I create an account?',
                    'a' => 'Click on the user icon in the top navigation bar and select "Register." Fill in your name, email, and password. You will receive a confirmation email to verify your account.',
                ],
                [
                    'q' => 'I forgot my password. How do I reset it?',
                    'a' => 'Click on "Log in" and then select "Forgot your password?" Enter your email address and we will send you a link to create a new password.',
                ],
                [
                    'q' => 'How do I update my personal information?',
                    'a' => 'Log in to your account and navigate to your profile. There you can update your name, email, phone number, and saved addresses.',
                ],
            ],
        ],

    ],

    'empty' => 'No questions in this category.',

    'cta' => [
        'heading' => 'Didn\'t find what you were looking for?',
        'body' => 'Our team is ready to help you with any questions.',
        'button' => 'Contact us',
    ],

];
