<?php
/*
 * Copyright (c) 2025 leoivard
 */

defined('ALTUMCODE') || die();

$available_notification_handlers = [
    'email' => [
        'icon' => 'fas fa-envelope',
        'emoji' => '📧',
        'color' => '#E57373',
        'background_color' => '#fef8f8',
    ],
    'webhook' => [
        'icon' => 'fas fa-satellite-dish',
        'emoji' => '📡',
        'color' => '#e73199',
        'background_color' => '#f9eef5',
    ],
    'slack' => [
        'icon' => 'fab fa-slack',
        'emoji' => '💼',
        'color' => '#9C27B0',
        'background_color' => '#faf2fb',
    ],
    'discord' => [
        'icon' => 'fab fa-discord',
        'emoji' => '🎧',
        'color' => '#7986CB',
        'background_color' => '#f8f9fc',
    ],
    'telegram' => [
        'icon' => 'fab fa-telegram',
        'emoji' => '💬',
        'color' => '#29B6F6',
        'background_color' => '#f5fbfe',
    ],
    'microsoft_teams' => [
        'icon' => 'fab fa-microsoft',
        'emoji' => '🔵',
        'color' => '#5C6BC0',
        'background_color' => '#f7f7fc',
    ],
    'twilio' => [
        'icon' => 'fas fa-sms',
        'emoji' => '📱',
        'color' => '#FFC107',
        'background_color' => '#fffcf3',
    ],
    'twilio_call' => [
        'icon' => 'fas fa-phone',
        'emoji' => '☎️',
        'color' => '#66BB6A',
        'background_color' => '#f7fcf7',
    ],
    'whatsapp' => [
        'icon' => 'fab fa-whatsapp',
        'emoji' => '🟢',
        'color' => '#4CAF50',
        'background_color' => '#fffcf3',
    ],
    'x' => [
        'icon' => 'fab fa-x-twitter',
        'emoji' => '🐥',
        'color' => '#f17206',
        'background_color' => '#000000',
    ],
    'google_chat' => [
        'icon' => 'fab fa-google',
        'emoji' => '📨',
        'color' => ['#DB4437', '#0F9D58', '#4285F4', '#F4B400'][array_rand(['#DB4437', '#0F9D58', '#4285F4', '#F4B400'])],
        'background_color' => '#f1fdf5',
    ],
];

if (settings()->internal_notifications->users_is_enabled) {
    $available_notification_handlers['internal_notification'] = [
        'icon' => 'fas fa-bell',
        'emoji' => '🔔',
        'color' => '#2800ff',
        'background_color' => '#000000',
    ];
}

if(\Altum\Plugin::is_active('push-notifications') && settings()->push_notifications->is_enabled) {
    $available_notification_handlers['push_subscriber_id'] = [
        'icon' => 'fas fa-thumbtack',
        'emoji' => '📌',
        'color' => '#24ba7f',
        'background_color' => '#24ba7f',
    ];
}

return $available_notification_handlers;
