<?php

namespace Altum\Controllers;

use Altum\Response;

defined('ALTUMCODE') || die();

class OnboardingProfile extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(empty($_POST)) redirect('dashboard');
        if(!\Altum\Csrf::check('global_token')) Response::json(l('global.error_message.invalid_csrf_token'), 'error');

        $phone_digits = preg_replace('/\D+/', '', input_clean($_POST['phone'] ?? '', 32));
        $industry = input_clean($_POST['industry'] ?? '', 32);
        $industries = ['restaurant', 'hotel', 'lounge', 'catering', 'corporate', 'retail', 'events', 'other'];

        if(!preg_match('/^\d{7,15}$/', $phone_digits)) Response::json('Please enter a valid phone number.', 'error');
        if(!in_array($industry, $industries, true)) Response::json('Please select your industry.', 'error');

        $user = db()->where('user_id', $this->user->user_id)->getOne('users', ['billing', 'extra']);
        $billing = json_decode($user->billing ?? '') ?: (object) [];
        $extra = json_decode($user->extra ?? '') ?: (object) [];
        $billing->phone = $phone_digits;
        $extra->signup_industry = $industry;

        db()->where('user_id', $this->user->user_id)->update('users', [
            'billing' => json_encode($billing),
            'extra' => json_encode($extra),
        ]);
        cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

        Response::json('', 'success', ['redirect_url' => url('plan?welcome_setup=1')]);
    }

}
