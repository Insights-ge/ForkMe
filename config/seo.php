<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Defaults
    |--------------------------------------------------------------------------
    |
    | These values are used as defaults for various SEO tags throughout the
    | application. You can override these in individual pages.
    |
    */

    'site_name' => config('app.name', 'ForkMe'),

    'gtm_id' => env('GTM_ID', 'GTM-WNFX5P9'),

    'social' => [
        'facebook' => 'https://www.facebook.com/your-page',
        'discord' => 'https://discord.gg/your-invite',
        'youtube' => 'https://www.youtube.com/your-channel',
        'twitter' => '@your-handle',
    ],

    'og' => [
        'image' => 'images/og/cover-1200x630.jpg',
        'image_width' => 1200,
        'image_height' => 630,
        'type' => 'website',
    ],

    'author' => 'Your Organization',

    'branding' => [
        'logo_main' => 'images/logos/main_logo.webp',
        'logo_header' => 'images/logo-light.avif',
        'logo_footer' => 'images/logo.svg',
    ],
];
