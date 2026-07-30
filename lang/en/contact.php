<?php

declare(strict_types=1);

return [

    'breadcrumb' => [
        'home' => 'Home',
        'contact' => 'Contact',
    ],

    'title' => 'Contact us',
    'subtitle' => 'We would love to hear from you — reach out through any channel below or send us a message.',

    'info' => [
        'heading' => 'Get in touch',
        'email_label' => 'Email',
        'phone_label' => 'Phone',
        'whatsapp_label' => 'WhatsApp',
        'hours_label' => 'Business hours',
        'hours_value' => 'Monday to Friday, 9:00 AM – 6:00 PM',
        'social_label' => 'Follow us',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
    ],

    'form' => [
        'heading' => 'Send us a message',
        'name' => 'Name',
        'email' => 'Email',
        'subject' => 'Subject',
        'message' => 'Message',
        'counter' => ':count / :max characters',
        'submit' => 'Send message',
        'sending' => 'Sending...',
        'placeholders' => [
            'name' => 'Jane Doe',
            'email' => 'you@example.com',
            'subject' => 'How can we help?',
            'message' => 'Write your message here...',
        ],
    ],

    'success' => [
        'title' => 'Message sent',
        'message' => 'Thank you for reaching out. We will get back to you soon.',
        'new_message' => 'Send another message',
    ],

    'error' => [
        'throttled' => 'You have reached the message limit. Please try again in a few minutes or email us directly at :email.',
        'send_failed' => 'We could not send your message. Please try again or email us directly at :email.',
    ],

    'faq' => [
        'heading' => 'Have a quick question?',
        'body' => 'Check our frequently asked questions — you might find your answer right away.',
        'cta' => 'View FAQ',
    ],

    'mail' => [
        'subject' => 'New contact message: :subject',
    ],

];
