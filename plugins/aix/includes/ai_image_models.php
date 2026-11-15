<?php
/*
 * Copyright (c) 2023 leoivard
 */

return [
    'dall-e-2' => [
        'api' => 'openai',
        'name' => 'OpenAI Dall-E 2',
        'max_length' => 1000,
        'sizes' => [
            '256x256', '512x512', '1024x1024'
        ],
        'variants' => [1,2,3,4,5]
    ],

    'dall-e-3' => [
        'api' => 'openai',
        'name' => 'OpenAI Dall-E 3',
        'max_length' => 4000,
        'sizes' => [
            '1024x1024', '1792x1024', '1024x1792'
        ],
        'variants' => [1]
    ],

    'stablediffusion' => [
        'api' => 'clipdrop',
        'name' => 'ClipDrop StableDiffusion',
        'max_length' => 1000,
        'sizes' => [
            '1024x1024'
        ],
        'variants' => [1]
    ]
];
