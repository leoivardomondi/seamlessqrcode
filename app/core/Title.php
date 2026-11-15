<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum;



defined('ALTUMCODE') || die();

class Title {
    public static $full_title;
    public static $site_title;
    public static $page_title;

    public static function initialize($site_title) {

        self::$site_title = $site_title;

        /* Add the prefix if needed */
        $language_key = preg_replace('/-/', '_', \Altum\Router::$controller_key);

        if(\Altum\Router::$path != '') {
            $language_key = \Altum\Router::$path . '_' . $language_key;
        }

        /* Check if the default is viable and use it */
        $page_title = (l($language_key . '.title')) ? l($language_key . '.title') : \Altum\Router::$controller;

        self::set($page_title);
    }

    public static function set($page_title, $full = false) {

        self::$page_title = $page_title;

        self::$full_title = self::$page_title . ($full ? null : ' - ' . self::$site_title);

        /* Set title */
        Meta::set_social_title(self::$full_title);
    }


    public static function get() {

        return self::$full_title;

    }

}
