<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
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
