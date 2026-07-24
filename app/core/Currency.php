<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum;

defined('ALTUMCODE') || die();

class Currency {
    public static $currency = null;
    public static $default_currency = null;

    /* Languages directory path */
    public static $path = APP_PATH . 'languages/';

    public static function initialize() {

        self::$default_currency = settings()->payment->default_currency;
        self::$currency = self::$default_currency;

        if($geo_currency = self::get_geo_currency()) {
            self::$currency = $geo_currency;
        }

        if(is_logged_in() && \Altum\Authentication::$user->currency && array_key_exists(\Altum\Authentication::$user->currency, (array) settings()->payment->currencies)) {
            self::$currency = \Altum\Authentication::$user->currency;
        }

        if(isset($_COOKIE['set_currency']) && array_key_exists($_COOKIE['set_currency'], (array) settings()->payment->currencies)) {
            self::$currency = $_COOKIE['set_currency'];
        }

    }

    private static function get_geo_currency() {

        if(!function_exists('get_ip') || !function_exists('get_maxmind_reader_country')) {
            return null;
        }

        $country_code = isset($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'] != 'XX' ? mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) : null;
        $continent_code = null;

        if(!$country_code) {
            $ip = get_ip();

            if(!$ip) {
                return null;
            }

            try {
                $maxmind = (get_maxmind_reader_country())->get($ip);
            } catch(\Exception $exception) {
                return null;
            }

            $country_code = $maxmind['country']['iso_code'] ?? null;
            $continent_code = $maxmind['continent']['code'] ?? null;
        }

        $currency = null;

        if($country_code == 'KE') {
            $currency = 'KES';
        } elseif($country_code == 'US') {
            $currency = 'USD';
        } elseif($continent_code == 'EU' || in_array($country_code, self::get_europe_country_codes())) {
            $currency = 'EUR';
        }

        return $currency && array_key_exists($currency, (array) settings()->payment->currencies) ? $currency : null;
    }

    private static function get_europe_country_codes() {
        return [
            'AD', 'AL', 'AT', 'AX', 'BA', 'BE', 'BG', 'BY', 'CH', 'CY', 'CZ', 'DE',
            'DK', 'EE', 'ES', 'FI', 'FO', 'FR', 'GB', 'GG', 'GI', 'GR', 'HR', 'HU',
            'IE', 'IM', 'IS', 'IT', 'JE', 'LI', 'LT', 'LU', 'LV', 'MC', 'MD', 'ME',
            'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'RS', 'RU', 'SE', 'SI', 'SJ',
            'SK', 'SM', 'UA', 'VA', 'XK',
        ];
    }

}
