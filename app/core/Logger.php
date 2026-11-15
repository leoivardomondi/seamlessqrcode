<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum;

defined('ALTUMCODE') || die();

class Logger {

    public static function users($user_id, $type) {

        $ip = get_ip();

        /* Detect the location */
        try {
            $maxmind = (get_maxmind_reader_city())->get($ip);
        } catch(\Exception $exception) {
            /* :) */
        }
        $continent_code = isset($maxmind) && isset($maxmind['continent']) ? $maxmind['continent']['code'] : null;
        $country_code = isset($maxmind) && isset($maxmind['country']) ? $maxmind['country']['iso_code'] : null;
        $city_name = isset($maxmind) && isset($maxmind['city']) ? $maxmind['city']['names']['en'] : null;

        /* Detect extra details about the user */
        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);

        /* Detect extra details about the user */
        $browser_name = $whichbrowser->browser->name ?? null;
        $os_name = $whichbrowser->os->name ?? null;
        $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
        $device_type = get_this_device_type();

        db()->insert('users_logs', [
            'user_id'        => $user_id,
            'type'           => $type,
            'ip'             => $ip,
            'device_type'    => $device_type,
            'os_name'        => $os_name,
            'browser_name'   => $browser_name,
            'browser_language' => $browser_language,
            'continent_code' => $continent_code,
            'country_code'   => $country_code,
            'city_name'      => $city_name,
            'datetime'       => get_date(),
        ]);

    }

}
