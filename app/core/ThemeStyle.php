<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum;

defined('ALTUMCODE') || die();

class ThemeStyle {
    public static $themes = [
        'light' => [
            'ltr' => 'bootstrap.min.css',
            'rtl' => 'bootstrap-rtl.min.css'
        ],
        'dark' => [
            'ltr' => 'bootstrap-dark.min.css',
            'rtl' => 'bootstrap-dark-rtl.min.css'
        ],
    ];
    public static $theme = 'light';

    public static function get() {
        if(isset($_COOKIE['theme_style']) && array_key_exists($_COOKIE['theme_style'], self::$themes)) {
            self::$theme = input_clean($_COOKIE['theme_style']);
        }

        return self::$theme;
    }

    public static function get_file() {
        return (\Altum\Router::$path != 'admin' && settings()->theme->{self::get() . '_is_enabled'} ? 'custom-bootstrap/' : null ) . self::$themes[self::get()][l('direction')];
    }

    public static function set_default($theme) {
        self::$theme = $theme;
    }

}
