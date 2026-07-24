<?php

return [
    'donation' => [
        'type' => 'payment',
        'icon' => 'fa fa-hand-holding-usd',
        'color' => '#0d55ad',
        'has_statistics' => false,
        'display_dynamic_name' => 'name',
        'whitelisted_thumbnail_image_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'fields' => [
            'title' => [
                'max_length' => 64
            ],
            'description' => [
                'max_length' => 256
            ],
            'currency' => [
                'max_length' => 4,
            ],
            'thank_you_title' => [
                'max_length' => 64
            ],
            'thank_you_description' => [
                'max_length' => 256
            ],
            'thank_you_url' => [
                'max_length' => 2048
            ],
            'email_notification' => [
                'max_length' => 320
            ],
            'webhook_url' => [
                'max_length' => 2048
            ],
        ],
        'category' => 'payments',
    ],
    'product' => [
        'type' => 'payment',
        'icon' => 'fa fa-cube',
        'color' => '#0d1bad',
        'has_statistics' => false,
        'display_dynamic_name' => 'name',
        'whitelisted_file_extensions' => ['pdf', 'zip'],
        'whitelisted_thumbnail_image_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'fields' => [
            'title' => [
                'max_length' => 64
            ],
            'description' => [
                'max_length' => 256
            ],
            'thank_you_title' => [
                'max_length' => 64
            ],
            'thank_you_description' => [
                'max_length' => 256
            ],
            'thank_you_url' => [
                'max_length' => 2048
            ],
            'currency' => [
                'max_length' => 4,
            ],
            'email_notification' => [
                'max_length' => 320
            ],
            'webhook_url' => [
                'max_length' => 2048
            ],
        ],
        'category' => 'payments',
    ],
    'service' => [
        'type' => 'payment',
        'icon' => 'fa fa-comments-dollar',
        'color' => '#3c0daa',
        'has_statistics' => false,
        'display_dynamic_name' => 'name',
        'whitelisted_thumbnail_image_extensions' => ['jpg', 'jpeg', 'png', 'svg', 'gif', 'webp'],
        'fields' => [
            'title' => [
                'max_length' => 64
            ],
            'description' => [
                'max_length' => 256
            ],
            'thank_you_title' => [
                'max_length' => 64
            ],
            'thank_you_description' => [
                'max_length' => 256
            ],
            'thank_you_url' => [
                'max_length' => 2048
            ],
            'currency' => [
                'max_length' => 4,
            ],
            'email_notification' => [
                'max_length' => 320
            ],
            'webhook_url' => [
                'max_length' => 2048
            ],
        ],
        'category' => 'payments',
    ],
];

