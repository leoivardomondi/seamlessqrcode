<?php

namespace Altum\Plugin;

use Altum\Plugin;

class Pwa {
    public static $plugin_id = 'pwa';

    public static function install() {

        /* Run the installation process of the plugin */
        $queries = [
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('pwa', '{}');",
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [
            "DELETE FROM `settings` WHERE `key` = 'pwa';",
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'uninstalled');

    }

    public static function activate() {
        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function disable() {
        return Plugin::save_status(self::$plugin_id, 'installed');
    }

}
