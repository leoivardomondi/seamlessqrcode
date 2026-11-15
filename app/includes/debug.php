<?php
/*
 * Copyright (c) 2025 leoivard
 */

defined('ALTUMCODE') || die();

if(DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 'Off');
}

if(LOGGING) {
    ini_set('log_errors', 1);
    ini_set('error_log', UPLOADS_PATH . 'logs/' . date('Y-m-d') . '.log');
} else {
    ini_set('log_errors', 0);
}

ini_set('html_errors', 0);
