<?php

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Logger;
use Altum\Models\User;
use Altum\Title;

defined('ALTUMCODE') || die();

class LifetimeCheckout extends Controller {

    public function index() {

        if(!settings()->payment->is_enabled) {
            throw_404();
        }

        $this->set_lifetime_currency();

        $plan_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $plans = (new \Altum\Models\Plan())->get_plans();
        $plan = $plans[$plan_id] ?? null;

        if(!$plan || !$plan->status || empty($plan->prices->lifetime->{currency()})) {
            redirect('lifetime#pricing');
        }

        if(is_logged_in()) {
            setcookie('set_currency', currency(), time() + 60 * 30, COOKIE_PATH);
            db()->where('user_id', \Altum\Authentication::$user_id)->update('users', ['currency' => currency()]);
            redirect('pay/' . $plan_id . '?payment_frequency=lifetime');
        }

        Title::set('Lifetime checkout');

        $values = [
            'name' => '',
            'email' => '',
        ];

        if(!empty($_POST)) {
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            $_POST['name'] = input_clean($_POST['name'] ?? '', 64);
            $_POST['email'] = input_clean_email($_POST['email'] ?? '');
            $values['name'] = $_POST['name'];
            $values['email'] = $_POST['email'];

            if(!$_POST['name']) {
                Alerts::add_field_error('name', l('global.error_message.empty_field'));
            }

            if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                Alerts::add_field_error('email', l('global.error_message.invalid_email'));
            }

            if(db()->where('email', $_POST['email'])->has('users')) {
                Alerts::add_error('An account with this email already exists. Please log in to continue to checkout.');
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $password = bin2hex(random_bytes(16));
                $email_code = md5($_POST['email'] . microtime());

                $registered_user = (new User())->create(
                    $_POST['email'],
                    $password,
                    $_POST['name'],
                    1,
                    'lifetime_checkout',
                    $email_code,
                    null,
                    0,
                    'free',
                    json_encode(settings()->plan_free->settings ?? ''),
                    get_date(),
                    settings()->main->default_timezone
                );

                db()->where('user_id', $registered_user['user_id'])->update('users', ['currency' => currency()]);
                setcookie('set_currency', currency(), time() + 60 * 30, COOKIE_PATH);

                Logger::users($registered_user['user_id'], 'register.success');

                $_SESSION['user_id'] = $registered_user['user_id'];
                $_SESSION['user_password_hash'] = md5($registered_user['password']);

                Logger::users($registered_user['user_id'], 'login.success');

                redirect('pay/' . $plan_id . '?payment_frequency=lifetime');
            }
        }

        $view = new \Altum\View('lifetime-checkout/index', (array) $this);
        $this->add_view_content('content', $view->run([
            'plan' => $plan,
            'plan_id' => $plan_id,
            'values' => $values,
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

        $currency = $this->is_european_visitor() ? 'EUR' : 'USD';
        \Altum\Currency::$currency = array_key_exists($currency, $currencies) ? $currency : settings()->payment->default_currency;
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
