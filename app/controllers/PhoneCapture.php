<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Response;

defined('ALTUMCODE') || die();

class PhoneCapture extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(empty($_POST)) {
            redirect(process_and_get_redirect_params() ?? 'dashboard');
        }

        if(!\Altum\Csrf::check('global_token')) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        $redirect = process_and_get_redirect_params() ?? 'dashboard';
        $redirect_url = url($redirect);

        if(($this->user->country ?? null) !== 'KE') {
            Response::json('', 'success', ['redirect_url' => $redirect_url]);
        }

        $user = db()->where('user_id', $this->user->user_id)->getOne('users', ['billing']);
        $billing = json_decode($user->billing ?? '');

        if(!$billing) {
            $billing = (object) [
                'type' => 'personal',
                'name' => '',
                'address' => '',
                'city' => '',
                'county' => '',
                'zip' => '',
                'country' => $this->user->country ?? 'KE',
                'phone' => '',
                'tax_id' => '',
                'notes' => '',
            ];
        }

        $raw_phone = input_clean($_POST['phone_number'] ?? '', 32);
        $phone_number = preg_replace('/\D+/', '', $raw_phone);

        /* Normalize Kenyan phone formats (+254... / 254... / 7... / 1...) */
        if(str_starts_with($phone_number, '254') && strlen($phone_number) >= 12) {
            $phone_number = '0' . substr($phone_number, 3, 9);
        } elseif((str_starts_with($phone_number, '7') || str_starts_with($phone_number, '1')) && strlen($phone_number) == 9) {
            $phone_number = '0' . $phone_number;
        } elseif(strlen($phone_number) > 10) {
            $phone_number = substr($phone_number, 0, 10);
        }

        if(!preg_match('/^(07|01)\d{8}$/', $phone_number)) {
            Response::json(l('welcome_phone_capture.error'), 'error');
        }

        $billing->phone = $phone_number;

        db()->where('user_id', $this->user->user_id)->update('users', [
            'billing' => json_encode($billing),
        ]);

        cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

        /* Clear the stored redirect after the number has been captured successfully */
        unset($_SESSION['redirect']);

        $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'welcome_setup=1';
        Response::json('', 'success', ['redirect_url' => $redirect_url]);
    }

}
