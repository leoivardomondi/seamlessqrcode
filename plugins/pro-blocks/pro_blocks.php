<?php

return [
    'applemusic' => [
        'type' => 'pro',
        'icon' => 'fab fa-apple',
        'color' => '#FA2D48',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['music.apple.com'],
        'category' => 'embeds',
    ],
    'tidal' => [
        'type' => 'pro',
        'icon' => 'fa fa-braille',
        'color' => '#000000',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['tidal.com'],
        'category' => 'embeds',
    ],
    'anchor' => [
        'type' => 'pro',
        'icon' => 'fa fa-anchor',
        'color' => '#8940FA',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['anchor.fm'],
        'category' => 'embeds',
    ],
    'twitter_profile' => [
        'type' => 'pro',
        'icon' => 'fab fa-twitter',
        'color' => '#1DA1F2',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['twitter.com'],
        'category' => 'embeds',
    ],
    'twitter_tweet' => [
        'type' => 'pro',
        'icon' => 'fab fa-twitter',
        'color' => '#1DA1F2',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['twitter.com'],
        'category' => 'embeds',
    ],
    'pinterest_profile' => [
        'type' => 'pro',
        'icon' => 'fab fa-pinterest',
        'color' => '#c8232c',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['pinterest.com'],
        'category' => 'embeds',
    ],
    'instagram_media' => [
        'type' => 'pro',
        'icon' => 'fab fa-instagram',
        'color' => '#F56040',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['www.instagram.com'],
        'category' => 'embeds',
    ],
    'rss_feed' => [
        'type' => 'pro',
        'icon' => 'fa fa-rss',
        'color' => '#ee802f',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'category' => 'advanced',
    ],
    'custom_html' => [
        'type' => 'pro',
        'icon' => 'fa fa-code',
        'color' => '#02234c',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'max_length' => 16384,
        'category' => 'advanced',
    ],
    'vcard' => [
        'type' => 'pro',
        'icon' => 'fa fa-id-card',
        'color' => '#FAB005',
        'has_statistics' => true,
        'display_dynamic_name' => 'name',
        'whitelisted_thumbnail_image_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'fields' => [
            'first_name' => [
                'max_length' => 64,
            ],
            'last_name' => [
                'max_length' => 64,
            ],
            'email' => [
                'max_length' => 320,
            ],
            'url' => [
                'max_length' => 1024,
            ],
            'company' => [
                'max_length' => 64,
            ],
            'job_title' => [
                'max_length' => 64,
            ],
            'birthday' => [
                'max_length' => 16,
            ],
            'street' => [
                'max_length' => 128,
            ],
            'city' => [
                'max_length' => 64,
            ],
            'zip' => [
                'max_length' => 32,
            ],
            'region' => [
                'max_length' => 32,
            ],
            'country' => [
                'max_length' => 32,
            ],
            'note' => [
                'max_length' => 256,
            ],
            'phone_number' => [
                'max_length' => 32,
            ],
            'social_label' => [
                'max_length' => 32
            ],
            'social_value' => [
                'max_length' => 1024
            ]
        ],
        'category' => 'advanced',
    ],
    'image_grid' => [
        'type' => 'pro',
        'icon' => 'fa fa-images',
        'color' => '#183153',
        'has_statistics' => true,
        'display_dynamic_name' => false,
        'whitelisted_image_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'category' => 'standard',
    ],
    'divider' => [
        'type' => 'pro',
        'icon' => 'fa fa-grip-lines',
        'color' => '#30a85a',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'category' => 'standard',
    ],
    'list' => [
        'type' => 'pro',
        'icon' => 'fa fa-list',
        'color' => '#2b385e',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'category' => 'standard',
    ],
    'alert' => [
        'type' => 'pro',
        'icon' => 'fa fa-bell',
        'color' => '#1500ff',
        'has_statistics' => true,
        'display_dynamic_name' => false,
        'category' => 'advanced',
    ],
    'tiktok_profile' => [
        'type' => 'pro',
        'icon' => 'fab fa-tiktok',
        'color' => '#FD3E3E',
        'has_statistics' => false,
        'display_dynamic_name' => false,
        'whitelisted_hosts' => ['www.tiktok.com'],
        'category' => 'embeds',
    ],
];

