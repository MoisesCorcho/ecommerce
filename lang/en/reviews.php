<?php

declare(strict_types=1);

return [
    'model' => [
        'label' => 'review',
        'plural' => 'reviews',
    ],

    'navigation' => [
        'label' => 'Reviews',
    ],

    'pages' => [
        'view_title' => 'Review',
        'list_title' => 'Reviews',
    ],

    'sections' => [
        'details' => 'Review details',
        'moderation' => 'Moderation',
        'content' => 'Content',
    ],

    'fields' => [
        'product' => 'Product',
        'user' => 'Customer',
        'rating' => 'Rating',
        'comment' => 'Comment',
        'is_approved' => 'Approved',
        'is_verified_purchase' => 'Verified purchase',
        'created_at' => 'Created',
        'updated_at' => 'Updated',
    ],

    'actions' => [
        'approve' => 'Approve',
        'unapprove' => 'Unapprove',
        'delete' => 'Delete',
        'submit' => 'Submit review',
        'update' => 'Update review',
        'delete_own' => 'Delete my review',
    ],

    'filters' => [
        'is_approved' => 'Approval',
        'approved_only' => 'Approved only',
        'pending_only' => 'Pending only',
        'all' => 'All',
        'is_verified' => 'Verified purchase',
        'verified_only' => 'Verified only',
        'unverified_only' => 'Unverified only',
    ],

    'status' => [
        'pending_moderation' => 'Pending moderation',
        'approved' => 'Approved',
        'verified_purchase' => 'Verified purchase',
    ],

    'empty' => [
        'heading' => 'No reviews yet',
        'description' => 'Product reviews from customers will appear here for moderation.',
        'no_reviews' => 'No reviews yet for this product.',
        'no_comment' => 'No comment',
    ],

    'notifications' => [
        'approved' => 'Review approved',
        'unapproved' => 'Review unapproved',
        'deleted' => 'Review deleted',
        'saved' => 'Review saved',
        'saved_pending' => 'Review submitted and pending moderation',
        'updated_pending' => 'Review updated and pending moderation again',
    ],

    'ui' => [
        'section_title' => 'Reviews',
        'average_label' => 'Average rating',
        'count_label' => ':count review|:count reviews',
        'your_review' => 'Your review',
        'write_review' => 'Write a review',
        'edit_review' => 'Edit your review',
        'login_required' => 'Sign in to leave a review.',
        'not_eligible' => 'You can leave a review after purchasing this product.',
        'pending_notice' => 'Your review is pending moderation and is not public yet.',
        'rating_required' => 'Choose a rating from 1 to 5.',
        'comment_placeholder' => 'Share your experience (optional)',
        'stars' => ':rating of 5 stars',
        'delete_confirm' => 'Delete this review permanently?',
        'new_variants_available' => 'You have purchased new variants for this product. Consider updating your review.',
    ],

    'errors' => [
        'unauthenticated' => 'You must be signed in to manage reviews.',
        'not_eligible' => 'You can only review products you have purchased.',
        'already_exists' => 'You already reviewed this product. Update your existing review instead.',
        'forbidden' => 'You are not allowed to perform this action on this review.',
        'invalid_rating' => 'Rating must be an integer between 1 and 5.',
        'comment_too_long' => 'Comment may not be greater than 2000 characters.',
        'not_found' => 'Review not found.',
        'rate_limited' => 'Too many review attempts. Please wait a moment.',
    ],

    'validation' => [
        'rating_required' => 'A rating is required.',
        'rating_integer' => 'Rating must be an integer.',
        'rating_between' => 'Rating must be between 1 and 5.',
        'comment_max' => 'Comment may not be greater than 2000 characters.',
    ],
];
