<?php

declare(strict_types=1);

return [
    'categories' => [
        'model' => [
            'label' => 'Blog Category',
            'plural' => 'Blog Categories',
        ],
        'sections' => [
            'content' => 'Bilingual Content',
            'content_description' => 'Translatable name and description for this category.',
            'settings' => 'Settings & Visibility',
            'settings_description' => 'Display priority and active status on storefront.',
        ],
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'slug_helper' => 'Unique URL identifier for category filtering.',
            'description' => 'Description',
            'sort_order' => 'Display Order',
            'sort_order_helper' => 'Lower number = higher priority in tabs and filters.',
            'is_active' => 'Active',
            'posts_count' => 'Articles',
            'created_at' => 'Created',
        ],
        'empty' => [
            'heading' => 'No blog categories yet',
            'description' => 'Create the first category to organize articles.',
        ],
        'notifications' => [
            'created' => 'Blog category created',
            'updated' => 'Blog category updated',
        ],
    ],

    'posts' => [
        'model' => [
            'label' => 'Article',
            'plural' => 'Articles',
        ],
        'sections' => [
            'content' => 'Article Content',
            'content_description' => 'Title, excerpt, and article body in multiple languages.',
            'settings' => 'Publishing & Media',
            'settings_description' => 'Category, publishing status, scheduled date, and cover image.',
            'seo' => 'Search Engine Optimization (SEO)',
            'seo_description' => 'Custom metadata for Google and social media previews.',
        ],
        'badges' => [
            'primary' => 'Primary',
        ],
        'fields' => [
            'title' => 'Title',
            'slug' => 'Slug',
            'slug_helper' => 'Unique canonical URL slug at `/blog/{slug}`.',
            'excerpt' => 'Excerpt',
            'excerpt_helper' => 'Short summary shown in blog listing and article cards.',
            'content' => 'Article Body',
            'cover_image' => 'Cover Image',
            'cover_image_helper' => 'Main editorial photography (16:10 or 4:3 format recommended).',
            'category' => 'Category',
            'author' => 'Author',
            'status' => 'Status',
            'published_at' => 'Publish Date',
            'published_at_helper' => 'If scheduled for the future, the article will not be visible until that date.',
            'meta_title' => 'Meta Title (SEO)',
            'meta_title_helper' => 'Optional. If empty, article title will be used.',
            'meta_description' => 'Meta Description (SEO)',
            'meta_description_helper' => 'Optional. If empty, article excerpt will be used.',
            'reading_time' => 'Estimated Time',
            'created_at' => 'Creation Date',
        ],
        'empty' => [
            'heading' => 'No articles yet',
            'description' => 'Write your first story to share with your readers.',
        ],
        'notifications' => [
            'created' => 'Article published or saved',
            'updated' => 'Article updated',
        ],
    ],

    'storefront' => [
        'hero_title' => 'The Leen Journal',
        'hero_subtitle' => 'Stories of craftsmanship, design, and timeless living',
        'hero_description' => 'Explore our articles, styling guides, and the artisanal universe behind each of our leather pieces.',
        'all_categories' => 'All articles',
        'reading_time' => ':min min read',
        'read_more' => 'Read story',
        'published_on' => 'Published on :date',
        'by_author' => 'By :author',
        'related_posts_heading' => 'You might also like',
        'related_posts_subtitle' => 'Discover more stories and editorial guides',
        'empty_heading' => 'No articles in this section yet',
        'empty_description' => 'We are preparing new stories. Check back soon for our latest updates.',
        'back_to_blog' => 'Back to blog',
        'share' => 'Share story',
        'preview_notice' => 'Preview Mode: This post is a draft or scheduled for the future and is only visible to administrators.',
        'show_more' => 'Show more',
        'show_less' => 'Show less',
        'previous_categories' => 'Previous categories',
        'next_categories' => 'Next categories',
        'search_placeholder' => 'Search stories, guides or topics...',
        'clear_search' => 'Clear search',
        'reset_filters' => 'Reset filters',
        'no_search_results' => 'We couldn\'t find any articles matching ":term".',
    ],
];
