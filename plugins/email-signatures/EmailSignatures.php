<?php

namespace Altum\Plugin;

use Altum\Plugin;

class EmailSignatures {
    public static $plugin_id = 'email-signatures';

    public static function install() {

        /* Run the installation process of the plugin */
        $queries = [
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('signatures', '');",

            "CREATE TABLE `signatures` (
              `signature_id` bigint unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int DEFAULT NULL,
              `project_id` int DEFAULT NULL,
              `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `template` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
              `datetime` datetime DEFAULT NULL,
              `last_datetime` datetime DEFAULT NULL,
              PRIMARY KEY (`signature_id`),
              KEY `project_id` (`project_id`),
              CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE
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
            "DELETE FROM `settings` WHERE `key` = 'signatures';",
            "DROP TABLE `signatures`;",
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
