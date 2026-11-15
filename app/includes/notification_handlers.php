<?php
/*
 * Copyright (c) 2025 leoivard
 */

defined('ALTUMCODE') || die();

$enabled_notification_handlers = [];

foreach(require APP_PATH . 'includes/available_notification_handlers.php' as $type => $notification_handler) {
    if(settings()->notification_handlers->{$type . '_is_enabled'}) {
        $enabled_notification_handlers[$type] = $notification_handler;
    }
}

return $enabled_notification_handlers;
