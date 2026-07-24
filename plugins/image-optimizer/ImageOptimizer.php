<?php

namespace Altum\Plugin;

use Altum\Alerts;
use Altum\Plugin;

class ImageOptimizer {
    public static $plugin_id = 'image-optimizer';

    public static function optimize($file_path, $future_file_name) {
        $optimizer = new \ArtisansWeb\Optimizer();
        $optimizer->root_dir = \Altum\Plugin::get(self::$plugin_id)->path;
        $optimizer->optimize($file_path, '', $future_file_name);
    }

    public static function install() {

        /* Check and make sure some required functions are available */
        if(!function_exists('mime_content_type')) {
            Alerts::add_error(sprintf(l('global.error_message.function_required'), 'mime_content_type()'));
            redirect('admin/plugins');
        }

        if(!function_exists('curl_version')) {
            Alerts::add_error(sprintf(l('global.error_message.function_required'), 'CURL'));
            redirect('admin/plugins');
        }

        if(!extension_loaded('gd') || !function_exists('gd_info')) {
            Alerts::add_error(sprintf(l('global.error_message.function_required'), 'GD'));
            redirect('admin/plugins');
        }

        /* Run the installation process of the plugin */
        $queries = [];

        foreach($queries as $query) {
            database()->query($query);
        }

        return Plugin::save_status(self::$plugin_id, 'active');

    }

    public static function uninstall() {

        /* Run the installation process of the plugin */
        $queries = [];

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
