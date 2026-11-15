<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum;

defined('ALTUMCODE') || die();

class Settings {
    public static $settings = null;

    public static function initialize() {

        self::$settings = (new \Altum\Models\Settings())->get();

    }

    public static function get() {
        return self::$settings;
    }
}
