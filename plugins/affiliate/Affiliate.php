<?php

namespace Altum\Plugin;

use Altum\Plugin;

class Affiliate {
    public static $plugin_id = 'affiliate';

    public static function install() {

        /* Run the installation process of the plugin */
        $queries = [
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('affiliate', '');",

            "CREATE TABLE IF NOT EXISTS `affiliates_commissions` (
            `affiliate_commission_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int DEFAULT NULL,
            `referred_user_id` int DEFAULT NULL,
            `payment_id` int unsigned DEFAULT NULL,
            `amount` float DEFAULT NULL,
            `currency` varchar(4) DEFAULT NULL,
            `is_withdrawn` tinyint(4) unsigned DEFAULT '0',
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`affiliate_commission_id`),
            UNIQUE KEY `affiliate_commission_id` (`affiliate_commission_id`),
            KEY `user_id` (`user_id`),
            KEY `referred_user_id` (`referred_user_id`),
            KEY `payment_id` (`payment_id`),
            CONSTRAINT `affiliates_commissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `affiliates_commissions_ibfk_2` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `affiliates_commissions_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS `affiliates_withdrawals` (
            `affiliate_withdrawal_id` bigint unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int DEFAULT NULL,
            `amount` float DEFAULT NULL,
            `currency` varchar(4) DEFAULT NULL,
            `note` varchar(1024) DEFAULT NULL,
            `affiliate_commissions_ids` text,
            `is_paid` tinyint(4) unsigned DEFAULT NULL,
            `datetime` datetime DEFAULT NULL,
            PRIMARY KEY (`affiliate_withdrawal_id`),
            UNIQUE KEY `affiliate_withdrawal_id` (`affiliate_withdrawal_id`),
            CONSTRAINT `affiliates_withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [
            "DELETE FROM `settings` WHERE `key` = 'affiliate';",
            "DROP TABLE IF EXISTS `affiliates_commissions`;",
            "DROP TABLE IF EXISTS `affiliates_withdrawals`;",
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
