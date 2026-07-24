<?php

namespace Altum\Plugin;

use Altum\Plugin;

class Offload {
    public static $plugin_id = 'offload';

    public static function install() {
        $settings = [
            'cdn_uploads_url' => '',
            'cdn_assets_url' => '',
            'assets_url' => '',
            'provider' => 'aws-s3',
            'endpoint_url' => '',
            'uploads_url' => '',
            'access_key' => '',
            'secret_access_key' => '',
            'storage_name' => '',
            'region' => '',
        ];

        database()->query(
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('offload', '" . database()->escape(json_encode($settings)) . "')"
        );

        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function uninstall() {
        database()->query("DELETE FROM `settings` WHERE `key` = 'offload'");

        return Plugin::save_status(self::$plugin_id, 'uninstalled');
    }

    public static function activate() {
        database()->query(
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('offload', '{\"cdn_uploads_url\":\"\",\"cdn_assets_url\":\"\",\"assets_url\":\"\",\"provider\":\"aws-s3\",\"endpoint_url\":\"\",\"uploads_url\":\"\",\"access_key\":\"\",\"secret_access_key\":\"\",\"storage_name\":\"\",\"region\":\"\"}')"
        );

        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function disable() {
        return Plugin::save_status(self::$plugin_id, 'installed');
    }
}
