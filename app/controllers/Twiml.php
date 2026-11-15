<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\controllers;

defined('ALTUMCODE') || die();

class Twiml extends Controller {

    public function index() {

        if(!settings()->notification_handlers->twilio_call_is_enabled) {
            redirect();
        }

        $language_key = isset($this->params[0]) ? str_replace('-', '_', input_clean($this->params[0])) : null;

        if(!$language_key) {
            redirect();
        }

        $available_language_keys = [
            'notification_handlers.test_title',
            'biolink_block.simple_notification',
            'guests_payments.simple_notification',
        ];

        if(!in_array($language_key, $available_language_keys)) {
            redirect();
        }

        /* Process parameters */
        $parameters = [];
        foreach($_GET as $key => $value) {
            if(string_starts_with('param', $key)) {
                $parameters[] = input_clean($value);
            }
        }

        header('Content-Type: text/xml');

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<Response>';
        echo '<Say>' . sprintf(l($language_key), ...$parameters) . '</Say>';
        echo '</Response>';

        die();
    }

}
