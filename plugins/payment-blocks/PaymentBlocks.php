<?php

namespace Altum\Plugin;

use Altum\Plugin;

class PaymentBlocks {
    public static $plugin_id = 'payment-blocks';

    public static function install() {

        /* Run the installation process of the plugin */
        $queries = [
            "CREATE TABLE `payment_processors` (
            `payment_processor_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int NOT NULL,
            `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `processor` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `settings` text COLLATE utf8mb4_unicode_ci,
            `is_enabled` tinyint unsigned NOT NULL DEFAULT '1',
            `last_datetime` datetime DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`payment_processor_id`),
            KEY `user_id` (`user_id`),
            CONSTRAINT `payment_processors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

            "CREATE TABLE `guests_payments` (
            `guest_payment_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `biolink_block_id` int DEFAULT NULL,
            `link_id` int DEFAULT NULL,
            `payment_processor_id` bigint unsigned DEFAULT NULL,
            `project_id` int DEFAULT NULL,
            `user_id` int DEFAULT NULL,
            `type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `processor` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `payment_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `email` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `total_amount` float DEFAULT NULL,
            `currency` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `data` text COLLATE utf8mb4_unicode_ci,
            `status` tinyint DEFAULT '0',
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`guest_payment_id`),
            KEY `biolink_block_id` (`biolink_block_id`),
            KEY `link_id` (`link_id`),
            KEY `payment_processor_id` (`payment_processor_id`),
            KEY `project_id` (`project_id`),
            KEY `user_id` (`user_id`),
            KEY `guests_payments_status_idx` (`status`) USING BTREE,
            CONSTRAINT `guests_payments_ibfk_1` FOREIGN KEY (`biolink_block_id`) REFERENCES `biolinks_blocks` (`biolink_block_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `guests_payments_ibfk_2` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `guests_payments_ibfk_3` FOREIGN KEY (`payment_processor_id`) REFERENCES `payment_processors` (`payment_processor_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `guests_payments_ibfk_4` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `guests_payments_ibfk_5` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [
            "DROP TABLE IF EXISTS `payment_processors`;",
            "DROP TABLE IF EXISTS `guests_payments`;",
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
