<?php

namespace Altum\Plugin;

use Altum\Meta;
use Altum\Plugin;
use Altum\Router;
use Altum\Uploads;

class DynamicOgImages {
    public static $plugin_id = 'dynamic-og-images';

    public static function install() {
        database()->query(
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('dynamic_og_images', '" . database()->escape(json_encode(self::get_default_settings())) . "')"
        );

        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function uninstall() {
        database()->query("DELETE FROM `settings` WHERE `key` = 'dynamic_og_images'");

        return Plugin::save_status(self::$plugin_id, 'uninstalled');
    }

    public static function activate() {
        database()->query(
            "INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('dynamic_og_images', '" . database()->escape(json_encode(self::get_default_settings())) . "')"
        );

        return Plugin::save_status(self::$plugin_id, 'active');
    }

    public static function disable() {
        return Plugin::save_status(self::$plugin_id, 'installed');
    }

    public static function process() {
        if(!isset(Router::$data['link'])) {
            return;
        }

        $link = Router::$data['link'];
        $settings = settings()->dynamic_og_images ?? (object) [];

        if(empty($settings->is_enabled)) {
            return;
        }

        $image_name = self::get_image_name($link);
        $image_path = Uploads::get_full_path('dynamic_og_images') . $image_name;
        $image_url = Uploads::get_full_url('dynamic_og_images') . $image_name;

        if(!file_exists($image_path)) {
            self::generate_local_image($link, $settings, $image_path);
        }

        if(file_exists($image_path)) {
            Meta::set_social_image($image_url);
        }
    }

    private static function get_default_settings() {
        return [
            'is_enabled' => false,
            'api_key' => '',
            'imagerypro_api_key' => '',
            'quality' => 90,
            'title' => settings()->main->title ?? '',
            'logo' => null,
            'background' => null,
            'screenshot_image_border_radius' => 30,
            'title_color' => '#ffffff',
            'background_color' => '#156A90',
            'refresh_interval' => 10,
        ];
    }

    private static function get_image_name($link) {
        $source = ($link->link_id ?? '') . '|' . ($link->url ?? '') . '|' . ($link->last_datetime ?? '') . '|' . (settings()->dynamic_og_images->refresh_interval ?? 10);

        return md5($source) . '.webp';
    }

    private static function generate_local_image($link, $settings, $image_path) {
        if(!extension_loaded('gd') || !function_exists('imagewebp')) {
            self::mark_pending($image_path);
            return;
        }

        $directory = dirname($image_path);
        if(!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        if(!is_writable($directory)) {
            return;
        }

        $width = 1200;
        $height = 630;
        $image = imagecreatetruecolor($width, $height);

        $background = self::hex_to_rgb($settings->background_color ?? '#156A90');
        $accent = self::adjust_color($background, -35);
        $title_color = self::hex_to_rgb($settings->title_color ?? '#ffffff');

        $background_color = imagecolorallocate($image, $background[0], $background[1], $background[2]);
        $accent_color = imagecolorallocate($image, $accent[0], $accent[1], $accent[2]);
        $text_color = imagecolorallocate($image, $title_color[0], $title_color[1], $title_color[2]);
        $muted_color = imagecolorallocatealpha($image, $title_color[0], $title_color[1], $title_color[2], 35);
        $panel_color = imagecolorallocatealpha($image, 255, 255, 255, 105);

        imagefilledrectangle($image, 0, 0, $width, $height, $background_color);
        imagefilledellipse($image, 1120, 70, 460, 460, $accent_color);
        imagefilledellipse($image, 80, 610, 520, 320, $accent_color);

        self::draw_background_image($image, $settings, $width, $height);

        $title = trim($settings->title ?? '') ?: (settings()->main->title ?? '');
        $subtitle = trim($link->settings->seo->title ?? '') ?: ($link->url ?? '');
        $url = $link->full_url ?? (SITE_URL . ($link->url ?? ''));

        self::draw_wrapped_text($image, $title, 72, 92, 5, $text_color, 46);
        self::draw_wrapped_text($image, $subtitle, 72, 224, 4, $muted_color, 72);

        imagefilledrectangle($image, 72, 474, 1128, 552, $panel_color);
        self::draw_wrapped_text($image, $url, 104, 505, 3, $text_color, 96);

        self::draw_logo($image, $settings, $width);

        imagewebp($image, $image_path, (int) ($settings->quality ?? 90));
        imagedestroy($image);

        $pending_path = preg_replace('/\.webp$/', '.pending', $image_path);
        if($pending_path && file_exists($pending_path)) {
            unlink($pending_path);
        }
    }

    private static function mark_pending($image_path) {
        $pending_path = preg_replace('/\.webp$/', '.pending', $image_path);
        if($pending_path && !file_exists($pending_path)) {
            file_put_contents($pending_path, '');
        }
    }

    private static function draw_background_image($image, $settings, $width, $height) {
        if(empty($settings->background)) {
            return;
        }

        $path = Uploads::get_full_path('logo_light') . $settings->background;
        $source = self::create_image_from_file($path);

        if(!$source) {
            return;
        }

        $source_width = imagesx($source);
        $source_height = imagesy($source);
        $scale = max($width / $source_width, $height / $source_height);
        $scaled_width = (int) ceil($source_width * $scale);
        $scaled_height = (int) ceil($source_height * $scale);
        $x = (int) (($width - $scaled_width) / 2);
        $y = (int) (($height - $scaled_height) / 2);

        imagecopyresampled($image, $source, $x, $y, 0, 0, $scaled_width, $scaled_height, $source_width, $source_height);
        imagefilter($image, IMG_FILTER_BRIGHTNESS, -45);
        imagedestroy($source);
    }

    private static function draw_logo($image, $settings, $width) {
        if(empty($settings->logo)) {
            return;
        }

        $path = Uploads::get_full_path('logo_light') . $settings->logo;
        $source = self::create_image_from_file($path);

        if(!$source) {
            return;
        }

        $source_width = imagesx($source);
        $source_height = imagesy($source);
        $target_width = min(220, $source_width);
        $target_height = (int) round($source_height * ($target_width / $source_width));
        $x = $width - $target_width - 72;
        $y = 72;

        imagecopyresampled($image, $source, $x, $y, 0, 0, $target_width, $target_height, $source_width, $source_height);
        imagedestroy($source);
    }

    private static function draw_wrapped_text($image, $text, $x, $y, $font, $color, $max_chars) {
        $lines = explode("\n", wordwrap($text, $max_chars, "\n", true));
        $line_height = imagefontheight($font) + 10;

        foreach(array_slice($lines, 0, 3) as $index => $line) {
            imagestring($image, $font, $x, $y + ($index * $line_height), $line, $color);
        }
    }

    private static function create_image_from_file($path) {
        if(!file_exists($path)) {
            return null;
        }

        $mime_type = mime_content_type($path);

        return match($mime_type) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private static function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');

        if(strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function adjust_color($rgb, $amount) {
        return [
            max(0, min(255, $rgb[0] + $amount)),
            max(0, min(255, $rgb[1] + $amount)),
            max(0, min(255, $rgb[2] + $amount)),
        ];
    }
}
