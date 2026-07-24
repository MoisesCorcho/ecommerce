<?php

declare(strict_types=1);

return [

    'meta' => [
        'page_title' => 'Sign in',
    ],

    'title' => 'Sign in',
    'subtitle' => 'Hello. Enter your details to access your account.',

    'fields' => [
        'email' => 'Email',
        'password' => 'Password',
    ],

    'placeholders' => [
        'email' => 'you@example.com',
    ],

    'actions' => [
        'submit' => 'Sign in',
        'submitting' => 'Signing in...',
        'show_password' => 'Show password',
        'hide_password' => 'Hide password',
    ],

    'links' => [
        'forgot_password' => 'Forgot your password?',
        'no_account' => "Don't have an account?",
        'register' => 'Create one',
        'back_to_store' => 'Back to store',
    ],

    'errors' => [
        'credentials' => 'Your credentials are incorrect. Check your email and password.',
        'email_required' => 'Email is required.',
        'email_invalid' => 'Enter a valid email address.',
        'password_required' => 'Password is required.',
    ],

];
