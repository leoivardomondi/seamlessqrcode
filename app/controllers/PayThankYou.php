<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;


defined('ALTUMCODE') || die();

class PayThankYou extends Controller {

    public function index() {

        $is_signed_guest = !empty($_GET['uid']) && !empty($_GET['sig']) && \Altum\Models\Payments::verify_direct_payment_signature((int) $_GET['uid'], (string) $_GET['sig']);
        if(!\Altum\Authentication::check() && !$is_signed_guest && empty($_GET['invoice_id'])) {
            \Altum\Authentication::guard();
        }

        if(!settings()->payment->is_enabled) {
            redirect('not-found');
        }

        $plan_id = $_GET['plan_id'] ?? null;

        /* Make sure it is either the trial / free plan or normal plans */
        switch($plan_id) {

            case 'free':

                /* Get the current settings for the free plan */
                $plan = settings()->plan_free;

                break;

            default:

                $plan_id = (int) $plan_id;

                /* Check if plan exists */
                if(!$plan = (new \Altum\Models\Plan())->get_plan_by_id($plan_id)) {
                    redirect('plan');
                }

                break;
        }

        /* Make sure the plan is enabled */
        if(!$plan->status) {
            redirect('plan');
        }

        /* Extra safety */
        $thank_you_url_parameters_raw = array_filter($_GET, function($key) {
            return !in_array($key, ['altum', 'unique_transaction_identifier']);
        }, ARRAY_FILTER_USE_KEY);


        $thank_you_url_parameters = '';
        foreach($thank_you_url_parameters_raw as $key => $value) {
            $thank_you_url_parameters .= '&' . $key . '=' . $value;
        }

        $unique_transaction_identifier = md5(\Altum\Date::get('', 4) . $thank_you_url_parameters);

        if($_GET['unique_transaction_identifier'] != $unique_transaction_identifier) {
            redirect('plan');
        }

        /* Flutterwave handle failed payments */
        if($_GET['payment_processor'] == 'flutterwave' && $_GET['status'] != 'successful') {
            redirect('pay/' . $_GET['plan_id'] . '?return_type=cancel&payment_processor=flutterwave');
        }

        /* Prepare the view */
        $data = [
            'plan_id'    => $plan_id,
            'plan'       => $plan,
        ];

        $view = new \Altum\View('pay-thank-you/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
