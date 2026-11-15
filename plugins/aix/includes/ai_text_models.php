<?php
/*
 * Copyright (c) 2023 leoivard
 */

return [
    'gpt-4o' => [
    'name' => 'GPT-4 Omni (Vision + Text)',
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
