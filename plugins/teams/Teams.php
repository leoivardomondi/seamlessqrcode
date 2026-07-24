<?php

namespace Altum\Plugin;

use Altum\Plugin;

class Teams {
    public static $plugin_id = 'teams';

    public static function install() {

        /* Run the installation process of the plugin */
        $queries = [
            "CREATE TABLE `teams` (
            `team_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int NOT NULL,
            `name` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `datetime` datetime NOT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`team_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `teams_members` (
            `team_member_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `team_id` bigint unsigned NOT NULL,
            `user_id` int DEFAULT NULL,
            `user_email` varchar(320) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `access` text COLLATE utf8mb4_unicode_ci,
            `status` tinyint unsigned DEFAULT '0',
            `datetime` datetime NOT NULL,
            `last_datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`team_member_id`),
            KEY `team_id` (`team_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `teams_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `teams_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [
            "DROP TABLE IF EXISTS `teams_members`;",
            "DROP TABLE IF EXISTS `teams`;",
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
