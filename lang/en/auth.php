<?php

return [

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'verify_email_required' => 'Please verify your email address before continuing.',

    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Confirm password',
        'terms' => 'I accept the terms and conditions',
    ],

    'login' => [
        'title' => 'Log in',
        'subtitle' => 'Hello. Enter your details to access your account.',
        'submit' => 'Log in',
        'submitting' => 'Signing in...',
        'show_password' => 'Show password',
        'hide_password' => 'Hide password',
        'no_account' => "Don't have an account?",
        'register_link' => 'Create one',
        'forgot_password' => 'Forgot your password?',
        'remember' => 'Remember me',
        'placeholders' => [
            'email' => 'you@example.com',
        ],
    ],

    'register' => [
        'title' => 'Create account',
        'subtitle' => 'Welcome. Fill in your details to get started.',
        'submit' => 'Create account',
        'submitting' => 'Creating account...',
        'show_password' => 'Show password',
        'hide_password' => 'Hide password',
        'have_account' => 'Already have an account?',
        'login_link' => 'Log in',
        'placeholders' => [
            'name' => 'Jane Doe',
            'email' => 'you@example.com',
        ],
        'strength' => [
            'weak' => 'Weak',
            'medium' => 'Medium',
            'strong' => 'Strong',
        ],
        'terms' => [
            'prefix' => 'I accept the',
            'terms_link' => 'Terms and Conditions',
            'connector' => 'and the',
            'privacy_link' => 'Privacy Policy',
        ],
    ],

    'forgot_password' => [
        'title' => 'Reset your password',
        'intro' => 'Enter your email and we will send you a link to reset your password.',
        'submit' => 'Send reset link',
        'back_to_login' => 'Back to login',
    ],

    'reset_password' => [
        'title' => 'Choose a new password',
        'submit' => 'Reset password',
    ],

    'verify_email' => [
        'title' => 'Verify your email',
        'intro' => 'We sent a verification link to :email. Click it to activate your account.',
        'resend' => 'Resend verification email',
        'resent' => 'Verification link sent.',
    ],

    'logout' => [
        'submit' => 'Log out',
    ],

];
