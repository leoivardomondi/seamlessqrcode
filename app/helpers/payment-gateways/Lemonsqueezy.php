<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\PaymentGateways;

/* Helper class for LemonSqueezy */
defined('ALTUMCODE') || die();

class Lemonsqueezy {
    static public $api_url = 'https://api.lemonsqueezy.com/v1/';
    static public $api_key = null;

    public static function get_headers() {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . self::$api_key,
            'Accept' => 'application/vnd.api+json'
        ];
    }

}
