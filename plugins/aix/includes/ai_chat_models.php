<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

return [
    'gpt-4o' => [
    'name' => 'GPT-4 Omni (Vision + Text)',
    'max_tokens' => 4096,
    'context_window' => 128000,
    'type' => 'chat_completions',
    ],
    'gpt-4-vision-preview' => [
        'name' => 'GPT 4 Vision Preview',
        'max_tokens' => 4000,
        'context_window' => 128000,
        'type' => 'chat_completions',
    ],
    'gpt-4-1106-preview' => [
        'name' => 'GPT 4 1106 Preview',
        'max_tokens' => 4096,
        'context_window' => 128000,
        'type' => 'chat_completions',
    ],
    'gpt-4' => [
        'name' => 'GPT 4',
        'max_tokens' => 8192,
        'context_window' => 8192,
        'type' => 'chat_completions',
    ],
    'gpt-3.5-turbo-1106' => [
        'name' => 'Updated GPT 3.5 Turbo',
        'max_tokens' => 4096,
        'context_window' => 16385,
        'type' => 'chat_completions',
    ],
    'gpt-3.5-turbo' => [
        'name' => 'GPT 3.5 Turbo',
        'max_tokens' => 4096,
        'context_window' => 4096,
        'type' => 'chat_completions',
    ],
];
