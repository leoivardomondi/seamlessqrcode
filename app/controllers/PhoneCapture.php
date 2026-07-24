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

        $phone_number = preg_replace('/\D+/', '', input_clean($_POST['phone_number'] ?? '', 32));

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

        Response::json('', 'success', ['redirect_url' => $redirect_url]);
    }

}
