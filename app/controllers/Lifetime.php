<?php
/*
 * Copyright (c) 2025 Leoivard (https://flowcode.co.ke/)
 */

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Lifetime extends Controller {

    public function index() {
        $this->set_lifetime_currency();

        // ✅ Set the page title
        Title::set('Lifetime Plans');

        // ✅ Load all plans
        $all_plans = (new \Altum\Models\Plan())->get_plans();

        // ✅ Filter only active plans with valid lifetime pricing
        $lifetime_plans = array_filter($all_plans, function($plan) {
            return $plan->status == 1 &&
                   isset($plan->prices->lifetime->{currency()}) &&
                   $plan->prices->lifetime->{currency()} > 0;
        });

        // ✅ Pass only lifetime plans into the partial view
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run(['plans' => $lifetime_plans]));

        // ✅ Render the main lifetime page view
        $view = new \Altum\View('lifetime/index', (array) $this);
        $this->add_view_content('content', $view->run([
            'type' => 'lifetime'
        ]));
    }

    private function set_lifetime_currency() {
        $currencies = (array) settings()->payment->currencies;

        if(isset($_COOKIE['set_currency']) && array_key_exists($_COOKIE['set_currency'], $currencies)) {
            \Altum\Currency::$currency = $_COOKIE['set_currency'];
            return;
        }

        if(is_logged_in() && \Altum\Authentication::$user->currency && array_key_exists(\Altum\Authentication::$user->currency, $currencies)) {
            \Altum\Currency::$currency = \Altum\Authentication::$user->currency;
            return;
        }

        \Altum\Currency::$currency = array_key_exists($this->is_european_visitor() ? 'EUR' : 'USD', $currencies)
            ? ($this->is_european_visitor() ? 'EUR' : 'USD')
            : settings()->payment->default_currency;
    }

    private function is_european_visitor() {
        $country_code = isset($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'] != 'XX' ? mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) : null;
        $continent_code = null;

        if(!$country_code && function_exists('get_ip') && function_exists('get_maxmind_reader_country')) {
            try {
                $maxmind = (get_maxmind_reader_country())->get(get_ip());
                $country_code = $maxmind['country']['iso_code'] ?? null;
                $continent_code = $maxmind['continent']['code'] ?? null;
            } catch(\Exception $exception) {
            }
        }

        return $continent_code == 'EU' || in_array($country_code, [
            'AD', 'AL', 'AT', 'AX', 'BA', 'BE', 'BG', 'BY', 'CH', 'CY', 'CZ', 'DE',
            'DK', 'EE', 'ES', 'FI', 'FO', 'FR', 'GB', 'GG', 'GI', 'GR', 'HR', 'HU',
            'IE', 'IM', 'IS', 'IT', 'JE', 'LI', 'LT', 'LU', 'LV', 'MC', 'MD', 'ME',
            'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'RS', 'RU', 'SE', 'SI', 'SJ',
            'SK', 'SM', 'UA', 'VA', 'XK',
        ]);
    }
}
