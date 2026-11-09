<?php
/*
 * Copyright (c) 2026 AltumCode (https://altumcode.com/)
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

use Altum\Alerts;
use Altum\Models\User;
use Altum\PaymentGateways\Lemonsqueezy;
use Altum\PaymentGateways\Paystack;
use Altum\PaymentGateways\Plisio;
use Altum\PaymentGateways\Revolut;
use Altum\Response;
use Altum\Title;

defined('ALTUMCODE') || die();

class Pay extends Controller {
    public $plan_id;
    public $return_type;
    public $payment_processor;
    public $plan;
    public $plan_taxes;
    public $applied_taxes_ids = [];
    public $code = null;
    public $payment_extra_data = null;

    public function index() {

        $invoice_id = isset($_GET['invoice_id']) ? (int) $_GET['invoice_id'] : null;

        if(!\Altum\Authentication::check()) {
            if($invoice_id && ($invoice = db()->where('invoice_id', $invoice_id)->getOne('invoices'))) {
                /* Public pay now link for invoice linked to customer email */
                $customer_user = db()->where('user_id', $invoice->user_id)->getOne('users');
                if($customer_user) {
                    $customer_user->billing = json_decode($customer_user->billing ?? '');
                    $customer_user->preferences = json_decode($customer_user->preferences ?? '');
                    $customer_user->plan_trial_done = 1;
                    $this->user = $customer_user;
                } else {
                    \Altum\Authentication::guard();
                }
            } else {
                \Altum\Authentication::guard();
            }
        }

        if(!settings()->payment->is_enabled) {
            throw_404();
        }

        (new \Altum\Models\User())->check_and_enforce_trial_eligibility($this->user);

        /* Ensure trial is never shown if user has ever had a subscription, paid plan, invoice, or payment */
        $is_subscribed_user = (
            !empty($this->user->plan_trial_done)
            || ($this->user->plan_id != 'free')
            || !empty($this->user->payment_processor)
            || isset($_GET['trial_skip'])
            || isset($_GET['invoice_id'])
            || isset($_GET['subscription_id'])
            || db()->where('user_id', $this->user->user_id)->has('subscriptions')
            || db()->where('user_id', $this->user->user_id)->where('status', 1)->has('payments')
            || db()->where('user_id', $this->user->user_id)->has('invoices')
        );

        if($is_subscribed_user) {
            $this->user->plan_trial_done = 1;
            $_GET['trial_skip'] = 'true';
            if((int) ($this->user->plan_trial_done ?? 0) !== 1) {
                db()->where('user_id', $this->user->user_id)->update('users', ['plan_trial_done' => 1]);
                cache()->deleteItemsByTag('user_id=' . $this->user->user_id);
            }
        }

        /* If user is paying an invoice or has invoice_id in URL, force trial skip */
        if(isset($_GET['invoice_id'])) {
            $_GET['trial_skip'] = 'true';
        }

        $payment_processors = require APP_PATH . 'includes/payment_processors.php';

        if(isset($_GET['currency'])) {
            $_GET['currency'] = mb_strtoupper(query_clean($_GET['currency']));

            if(array_key_exists($_GET['currency'], (array) settings()->payment->currencies)) {
                \Altum\Currency::$currency = $_GET['currency'];
            }
        }

        $this->plan_id = isset($this->params[0]) ? $this->params[0] : null;
        $this->return_type = isset($_GET['return_type']) && in_array($_GET['return_type'], ['success', 'cancel']) ? $_GET['return_type'] : null;
        $this->payment_processor = isset($_GET['payment_processor']) && array_key_exists($_GET['payment_processor'], $payment_processors) ? $_GET['payment_processor'] : null;

        /* ^_^ */
        switch($this->plan_id) {
            case 'free':

                $this->plan = settings()->plan_free;

                if($this->user->plan_id == 'free') {
                    Alerts::add_info(l('pay.free.free_already'));
                } else {
                    Alerts::add_info(l('pay.free.other_plan_not_expired'));
                }

                redirect('plan');

                break;

            default:

                $this->plan_id = (int) $this->plan_id;

                $plans = (new \Altum\Models\Plan())->get_plans();

                /* Check if plan exists */
                $this->plan = $plans[$this->plan_id] ?? null;
                if(!$this->plan) {
                    redirect('plan');
                }

                /* Check for potential taxes */
                $this->plan_taxes = (new \Altum\Models\Plan())->get_plan_taxes_by_taxes_ids($this->plan->taxes_ids);

                /* Filter them out */
                if($this->plan_taxes) {
                    foreach($this->plan_taxes as $key => $value) {

                        $user_billing_type = $this->user->billing->type ?? 'personal';
                        $user_billing_country = $this->user->billing->country ?? null;
                        $user_billing_state = $this->user->billing->state ?? null;
                        $user_billing_county = $this->user->billing->county ?? null;

                        /* Type */
                        if($value->billing_type != $user_billing_type && $value->billing_type != 'both') {
                            unset($this->plan_taxes[$key]);
                        }

                        /* Countries */
                        if($value->countries && (!empty($user_billing_country) ? !in_array($user_billing_country, $value->countries) : false)) {
                            unset($this->plan_taxes[$key]);
                        }

                        /* State */
                        if(!empty($value->state) && $value->state != $user_billing_state) {
                            unset($this->plan_taxes[$key]);
                        }

                        /* County */
                        if(!empty($value->county) && $value->county != $user_billing_county) {
                            unset($this->plan_taxes[$key]);
                        }

                        /* Add the tax */
                        if(isset($this->plan_taxes[$key])) {
                            $this->applied_taxes_ids[] = (int) $value->tax_id;
                        }

                    }

                    $this->plan_taxes = array_values($this->plan_taxes);
                }

                break;
        }

        /* Make sure the plan is enabled */
        if(!$this->plan->status) {
            redirect('plan');
        }

        /* Allow users to pay directly without filling billing details first; they can update billing info later for invoice generation */
        /*
        if(
            settings()->payment->taxes_and_billing_is_enabled
            && ($this->user->plan_trial_done || !$this->plan->trial_days || isset($_GET['trial_skip']))
            && (empty($this->user->billing->name) || empty($this->user->billing->address) || empty($this->user->billing->city) || empty($this->user->billing->county) || empty($this->user->billing->zip))
        ) {
            redirect('pay-billing/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
        }
        */

        /* Form submission processing */
        if(!empty($_POST) && !$this->return_type) {

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');
            //ALTUMCODE:DEMO if(DEMO) redirect('pay/' . $this->plan_id . (isset($_GET['trial_skip']) ? '?trial_skip=true' : null));

            /* Check for code usage */
            if(settings()->payment->codes_is_enabled && isset($_POST['code'])) {
                $_POST['code'] = query_clean($_POST['code']);
                $this->code = database()->query("SELECT * FROM `codes` WHERE `code` = '{$_POST['code']}' AND `redeemed` < `quantity`")->fetch_object();

                if($this->code) {
                    $start_datetime = $this->code->start_datetime ?? null;
                    $end_datetime = $this->code->end_datetime ?? null;

                    if($start_datetime && $start_datetime > date('Y-m-d H:i:s')) {
                        $this->code = null;
                    }

                    if($end_datetime && $end_datetime < date('Y-m-d H:i:s')) {
                        $this->code = null;
                    }

                    if($this->code) {
                        $this->code->plans_ids = json_decode($this->code->plans_ids ?? '[]');

                        if(db()->where('user_id', $this->user->user_id)->where('code_id', $this->code->code_id)->has('redeemed_codes')) {
                            $this->code = null;
                        }
                    }

                    if($this->code) {
                        if(!is_array($this->code->plans_ids)) {
                            $this->code->plans_ids = [];
                        }

                        if(!in_array($this->plan_id, $this->code->plans_ids)) {
                            $this->code = null;
                        }
                    }
                }
            }

            /* Check for any errors */
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            /* Process further */
            if(!settings()->payment->trial_require_card && $this->plan->trial_days && !$this->user->plan_trial_done && !isset($_GET['trial_skip'])) {
                /* :) */
            } else if($this->code && $this->code->type == 'redeemable' && in_array($this->plan_id, $this->code->plans_ids)) {

                /* Cancel current subscription if needed */
                if($this->user->plan_id != $this->plan->plan_id) {
                    try {
                        (new User())->cancel_subscription($this->user->user_id);
                    } catch (\Exception $exception) {
                        Alerts::add_error($exception->getCode() . ':' . $exception->getMessage());
                        redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
                    }
                }

            } else {
                $_POST['payment_frequency'] = query_clean($_POST['payment_frequency']);
                $_POST['payment_processor'] = query_clean($_POST['payment_processor']);
                $_POST['payment_type'] = query_clean($_POST['payment_type']);

                /* Make sure the chosen option comply */
                if(!in_array($_POST['payment_frequency'], ['monthly', 'quarterly', 'biannual', 'annual', 'lifetime'])) {
                    redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
                }

                if(!array_key_exists($_POST['payment_processor'], $payment_processors)) {
                    redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
                } else {

                    /* Make sure the payment processor is active */
                    if(!settings()->{$_POST['payment_processor']}->is_enabled) {
                        redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
                    }

                }

                if(!in_array($_POST['payment_type'], ['one_time', 'recurring'])) {
                    redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
                }

                /* Lifetime */
                if($_POST['payment_frequency'] == 'lifetime') {
                    $_POST['payment_type'] = 'one_time';
                }

                /* Make sure recurring is available for the payment processor */
                if(!in_array('recurring', $payment_processors[$_POST['payment_processor']]['payment_type'])) {
                    $_POST['payment_type'] = 'one_time';
                }
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Check if we should start the trial or not */
                if(!settings()->payment->trial_require_card && $this->plan->trial_days && !$this->user->plan_trial_done && !isset($_GET['trial_skip'])) {

                    /* Determine the expiration date of the plan */
                    $plan_expiration_date = (new \DateTime())->modify('+' . $this->plan->trial_days . ' days')->format('Y-m-d H:i:s');
                    $plan_settings = json_encode($this->plan->settings ?? '');

                    /* Database query */
                    db()->where('user_id', $this->user->user_id)->update('users', [
                        'plan_id' => $this->plan_id,
                        'plan_settings' => $plan_settings,
                        'plan_expiration_date' => $plan_expiration_date,
                        'plan_trial_done' => 1,
                    ]);

                    /* Clear the cache */
                    cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

                    /* Success message and redirect */
                    $this->redirect_pay_thank_you();
                }

                /* Redeem */
                else if($this->code && $this->code->type == 'redeemable' && in_array($this->plan_id, $this->code->plans_ids)) {

                    $datetime = $this->user->plan_id == $this->plan->plan_id ? $this->user->plan_expiration_date : '';
                    $plan_expiration_date = (new \DateTime($datetime))->modify('+' . $this->code->days . ' days')->format('Y-m-d H:i:s');
                    $plan_settings = json_encode($this->plan->settings ?? '');

                    /* Database query */
                    db()->where('user_id', $this->user->user_id)->update('users', [
                        'plan_id' => $this->plan_id,
                        'plan_expiration_date' => $plan_expiration_date,
                        'plan_settings' => $plan_settings,
                        'plan_expiry_reminder' => 0,
                    ]);

                    /* Update the code usage */
                    db()->where('code_id', $this->code->code_id)->update('codes', ['redeemed' => db()->inc()]);

                    /* Add log for the redeemed code */
                    db()->insert('redeemed_codes', [
                        'code_id'   => $this->code->code_id,
                        'user_id'   => $this->user->user_id,
                        'datetime'  => get_date()
                    ]);

                    /* Send webhook notification if needed */
                    if(settings()->webhooks->code_redeemed) {
                        fire_and_forget('post', settings()->webhooks->code_redeemed, [
                            'user_id' => $this->user->user_id,
                            'email' => $this->user->email,
                            'name' => $this->user->name,
                            'plan_id' => $this->plan_id,
                            'plan_expiration_date' => $plan_expiration_date,
                            'code_id' => $this->code->code_id,
                            'code' => $this->code->code,
                            'code_name' => $this->code->name,
                            'redeemed_days' => $this->code->days,
                            'datetime' => get_date(),
                        ], signature: true);
                    }

                    /* Send admin notification if enabled */
                    if(settings()->email_notifications->new_code_redeemed && !empty(settings()->email_notifications->emails)) {

                        $email_template = get_email_template(
                            [
                                '{{CODE}}' => $this->code->code,
                            ],
                            l('global.emails.admin_new_code_redeemed_notification.subject'),
                            [
                                '{{CODE}}' => $this->code->code,
                                '{{REDEEMED_DAYS}}' => nr($this->code->days),
                                '{{NAME}}' => $this->user->name,
                                '{{EMAIL}}' => $this->user->email,
                                '{{PLAN_NAME}}' => $this->plan->name,
                                '{{USER_LINK}}' => url('admin/user-view/' . $this->user->user_id),
                            ],
                            l('global.emails.admin_new_code_redeemed_notification.body')
                        );

                        send_mail(
                            explode(',', settings()->email_notifications->emails),
                            $email_template->subject,
                            $email_template->body
                        );
                    }

                    /* Clear the cache */
                    cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

                    /* Success message and redirect */
                    $this->redirect_pay_thank_you();
                }

                else {
                    $this->{$_POST['payment_processor']}();
                }
            }

        }

        /* Include the detection of callbacks processing */
        $this->payment_return_process();

        /* Set a custom title */
        Title::set(sprintf(l('pay.title'), ($this->plan->translations->{\Altum\Language::$name}->name ?? '') ?: $this->plan->name));

        /* Prepare invoice message details if paying for an invoice */
        $invoice_data = null;
        if(!empty($_GET['invoice_id'])) {
            $inv = db()->where('invoice_id', (int) $_GET['invoice_id'])->getOne('invoices');
            if($inv) {
                $inv_metadata = json_decode($inv->metadata ?? '{}', true) ?: [];
                $sub = $inv->subscription_id ? db()->where('subscription_id', $inv->subscription_id)->getOne('subscriptions') : null;
                $period_start = $inv_metadata['period_start'] ?? ($sub->current_period_end ?? $inv->datetime);
                $freq = $inv->frequency ?: ($sub->frequency ?? 'monthly');
                $period_end = $inv_metadata['period_end'] ?? (new \Altum\Models\Subscription())->calculate_period_end($period_start, $freq);
                $invoice_data = [
                    'invoice_id' => $inv->invoice_id,
                    'invoice_number' => $inv->invoice_id,
                    'amount' => $inv->total_amount,
                    'currency' => $inv->currency,
                    'email' => $this->user->email ?? null,
                    'plan_name' => $this->plan->name ?? $inv->plan_id,
                    'frequency' => $freq,
                    'period_start' => $period_start,
                    'period_end' => $period_end,
                    'status' => $inv->status,
                ];
            }
        }

        /* Prepare the view */
        $data = [
            'plan_id'           => $this->plan_id,
            'plan'              => $this->plan,
            'plan_taxes'        => $this->plan_taxes,
            'payment_processors'=> $payment_processors,
            'payment_extra_data'=> $this->payment_extra_data,
            'invoice_data'      => $invoice_data,
            'total_users'       => \Altum\Cache::cache_function_result('total_users', [], function() {
                return db()->getValue('users', 'count(*)');
            })
        ];

        $view = new \Altum\View('pay/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    private function payment_return_process() {

        /* Return confirmation processing if successfully */
        if($this->return_type && $this->payment_processor && $this->return_type == 'success') {

            /* Redirect to the thank you page */
            $this->redirect_pay_thank_you();
        }

        /* Return confirmation processing if failed */
        if($this->return_type && $this->payment_processor && $this->return_type == 'cancel') {
            Alerts::add_error(l('pay.error_message.canceled_payment'));
            redirect('pay/' . $this->plan_id . '?' . (isset($_GET['trial_skip']) ? '&trial_skip=true' : null) . (isset($_GET['code']) ? '&code=' . $_GET['code'] : null));
        }

    }

    /* Ajax to check if discount codes are available */
    public function code() {
        \Altum\Authentication::guard();

        $_POST = json_decode(file_get_contents('php://input'), true);

        if(!\Altum\Csrf::check('global_token')) {
            die();
        }

        if(!settings()->payment->is_enabled || !settings()->payment->codes_is_enabled) {
            die();
        }

        if(empty($_POST)) {
            die();
        }

        $_POST['plan_id'] = (int) $_POST['plan_id'];
        $_POST['code'] = trim(query_clean($_POST['code']));

        if(!$plan = db()->where('plan_id', $_POST['plan_id'])->getOne('plans')) {
            Response::json(l('pay.error_message.code_invalid'), 'error');
        }

        /* Make sure the discount code exists */
        $code = database()->query("SELECT * FROM `codes` WHERE `code` = '{$_POST['code']}' AND `redeemed` < `quantity`")->fetch_object();

        if(!$code) {
            Response::json(l('pay.error_message.code_invalid'), 'error');
        }

        $start_datetime = $code->start_datetime ?? null;
        $end_datetime = $code->end_datetime ?? null;

		if($start_datetime && $start_datetime > date('Y-m-d H:i:s')) {
			Response::json(l('account_redeem_code.error_message.code_invalid'), 'error');
		}

		if($end_datetime && $end_datetime < date('Y-m-d H:i:s')) {
			Response::json(l('pay.error_message.code_invalid'), 'error');
		}

        $code->plans_ids = json_decode($code->plans_ids ?? '[]');

        if(!in_array($_POST['plan_id'], $code->plans_ids)) {
            Response::json(l('pay.error_message.code_invalid'), 'error');
        }

        if(db()->where('user_id', $this->user->user_id)->where('code_id', $code->code_id)->has('redeemed_codes')) {
            Response::json(l('pay.error_message.code_used'), 'error');
        }

		/* Display current uses for fomo */
		$discount_percentage_use_left = number_format(($code->redeemed / $code->quantity) * 100, 2, '.', '');

        Response::json(
            sprintf(l('pay.success_message.code'), '<strong>' . $code->discount . '%</strong>'),
            'success',
            [
                'code' => $code,
                'submit_text' => $code->type == 'redeemable' ? sprintf(l('pay.custom_plan.code_redeemable'), $code->days) : null,
                'discount_text' => $code->type == 'redeemable' ? null : sprintf(l('pay.custom_plan.code_discount'), $code->discount),
				'discount_usage_text' => $code->type == 'redeemable' || $discount_percentage_use_left <= 30 ? null : sprintf(l('pay.success_message.code_quantity_percentage_used'), nr($discount_percentage_use_left)),
				'discount_scheduled_end_text' => $code->type == 'redeemable' || empty($end_datetime) ? null : sprintf(l('pay.success_message.code_scheduled_end_datetime'), \Altum\Date::get_time_until($end_datetime)),
            ]
        );
    }

    /* Generate the generic return url parameters */
    private function return_url_parameters($return_type, $base_amount, $total_amount, $code, $discount_amount) {
        $parameters =
            '&return_type=' . $return_type
            . '&payment_processor=' . $_POST['payment_processor']
            . '&payment_frequency=' . $_POST['payment_frequency']
            . '&payment_type=' . $_POST['payment_type']
            . '&code=' . $code
            . '&discount_amount=' . $discount_amount
            . '&base_amount=' . $base_amount
            . '&total_amount=' . $total_amount
            . '&currency=' . currency();

        if(isset($_GET['invoice_id'])) {
            $parameters .= '&invoice_id=' . (int) $_GET['invoice_id'];
        }

        return $parameters;
    }

    /* Simple url generator to return the thank you page */
    private function redirect_pay_thank_you() {
        $thank_you_url_parameters_raw = array_filter($_GET, function($key) {
            return $key != 'altum';
        }, ARRAY_FILTER_USE_KEY);

        $thank_you_url_parameters = '&plan_id=' . $this->plan_id;
        $thank_you_url_parameters .= '&user_id=' . $this->user->user_id;

        /* Trial */
        if(!settings()->payment->trial_require_card && $this->plan->trial_days && !$this->user->plan_trial_done && !isset($_GET['trial_skip'])) {
            $thank_you_url_parameters .= '&trial_days=' . $this->plan->trial_days;
        }

        /* Redeemed */
        if($this->code && $this->code->type == 'redeemable' && in_array($this->plan_id, $this->code->plans_ids)) {
            $thank_you_url_parameters .= '&code_days=' . $this->code->days;
        }

        foreach($thank_you_url_parameters_raw as $key => $value) {
            $thank_you_url_parameters .= '&' . $key . '=' . $value;
        }

        $thank_you_url_parameters .= '&unique_transaction_identifier=' . md5(\Altum\Date::get('', 4) . $thank_you_url_parameters);

        redirect('pay-thank-you?' . $thank_you_url_parameters);
    }

    private function get_price_details() {
        /* Payment details */
        $price = $base_amount = (float) $this->plan->prices->{$_POST['payment_frequency']}->{currency()};
        $code = '';
        $discount_amount = 0;

        /* Check for code usage */
        if($this->code) {
            /* Discount amount */
            $discount_amount = number_format(($price * $this->code->discount / 100), 2, '.', '');

            /* Calculate the new price */
            $price = $price - $discount_amount;

            $code = $this->code->code;
        }

        $payment_frequency_days = match ($_POST['payment_frequency']) {
            'monthly' => 30,
            'quarterly' => 90,
            'biannual' => 180,
            'annual' => 365,
            default => 30,
        };

        return [
            'base_amount' => $base_amount,
            'price' => $price,
            'code' => $code,
            'discount_amount' => $discount_amount,
            'payment_frequency_days' => $payment_frequency_days,
        ];
    }

    private function calculate_price_with_taxes($discounted_price) {

        $price = $discounted_price;

        if($this->plan_taxes) {

            /* Check for the inclusives */
            $inclusive_taxes_total_percentage = 0;

            foreach($this->plan_taxes as $row) {
                if($row->type == 'exclusive') continue;

                $inclusive_taxes_total_percentage += $row->value;
            }

            $total_inclusive_tax = $price - ($price / (1 + $inclusive_taxes_total_percentage / 100));

            $price_without_inclusive_taxes = $price - $total_inclusive_tax;

            /* Check for the exclusives */
            $exclusive_taxes_array = [];

            foreach($this->plan_taxes as $row) {

                if($row->type == 'inclusive') {
                    continue;
                }

                $exclusive_tax = $row->value_type == 'percentage' ? $price_without_inclusive_taxes * ($row->value / 100) : $row->value;

                $exclusive_taxes_array[] = $exclusive_tax;

            }

            $exclusive_taxes = array_sum($exclusive_taxes_array);

            /* Price with all the taxes */
            $price_with_taxes = $price + $exclusive_taxes;

            $price = $price_with_taxes;
        }

        return $price;

    }

    private function paypal() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price based on currency */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        try {
            $paypal_api_url = \Altum\PaymentGateways\Paypal::get_api_url();
            $paypal_headers = \Altum\PaymentGateways\Paypal::get_headers();
        } catch (\Exception $exception) {
            Alerts::add_error($exception->getMessage());
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Create unique custom id for Paypal tracking */
        $paypal_custom_id = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);

        /* Handle payment types */
        switch ($_POST['payment_type']) {

            case 'one_time':

                $paypal_order_payload = [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [[
                        'amount' => [
                            'currency_code' => currency(),
                            'value' => $formatted_price,
                        ],
                        'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'custom_id' => $paypal_custom_id,
                    ]],
                    'application_context' => [
                        'brand_name' => settings()->business->brand_name ?: settings()->main->title,
                        'landing_page' => 'NO_PREFERENCE',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'PAY_NOW',
                        'return_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                        'cancel_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount))
                    ]
                ];

                $paypal_response = \Unirest\Request::post(
                    $paypal_api_url . 'v2/checkout/orders',
                    $paypal_headers,
                    \Unirest\Request\Body::json($paypal_order_payload)
                );

                if($paypal_response->code >= 400) {
                    $paypal_details = '';
                    if(isset($paypal_response->body->details) && is_array($paypal_response->body->details)) {
                        $paypal_details_array = array_map(function($detail) {
                            return trim(($detail->issue ?? '') . ' ' . ($detail->description ?? ''));
                        }, $paypal_response->body->details);
                        $paypal_details = ' ' . implode(' ', array_filter($paypal_details_array));
                    }

                    $paypal_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $paypal_response->body->name . ':' . $paypal_response->body->message . $paypal_details : l('pay.error_message.failed_payment');
                    Alerts::add_error($paypal_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                $paypal_payment_url = $paypal_response->body->links[1]->href;
                header('Location: ' . $paypal_payment_url); die();

                break;

            case 'recurring':

                $paypal_product_id = $this->plan_id . '_' . $_POST['payment_frequency'] . '_' . $formatted_price . '_' . currency();

                /* Ensure the Paypal product exists, create if missing */
                $product_response = \Unirest\Request::get(
                    $paypal_api_url . 'v1/catalogs/products/' . $paypal_product_id,
                    $paypal_headers
                );

                if($product_response->code == 404) {
                    $product_create_response = \Unirest\Request::post(
                        $paypal_api_url . 'v1/catalogs/products',
                        $paypal_headers,
                        \Unirest\Request\Body::json([
                            'id' => $paypal_product_id,
                            'name' => settings()->business->brand_name . ' - ' . $this->plan->name,
                            'type' => 'DIGITAL',
                        ])
                    );

                    if($product_create_response->code >= 400) {
                        $paypal_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $product_create_response->body->name . ':' . $product_create_response->body->message : l('pay.error_message.failed_payment');
                        Alerts::add_error($paypal_error_message);
                        redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                    }
                }

                /* Create Paypal billing plan */
                $plan_response = \Unirest\Request::post(
                    $paypal_api_url . 'v1/billing/plans',
                    $paypal_headers,
                    \Unirest\Request\Body::json([
                        'product_id' => $paypal_product_id,
                        'name' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'description' => l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'status' => 'ACTIVE',
                        'billing_cycles' => [[
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'currency_code' => currency(),
                                    'value' => $formatted_price
                                ]
                            ],
                            'frequency' => [
                                'interval_unit' => 'DAY',
                                'interval_count' => $payment_frequency_days
                            ],
                            'tenure_type' => 'REGULAR',
                            'sequence' => 1,
                            'total_cycles' => 0
                        ]],
                        'payment_preferences' => [
                            'auto_bill_outstanding' => true,
                            'setup_fee' => [
                                'currency_code' => currency(),
                                'value' => $formatted_price
                            ],
                            'setup_fee_failure_action' => 'CANCEL',
                            'payment_failure_threshold' => 0
                        ]
                    ])
                );

                if($plan_response->code >= 400) {
                    $paypal_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $plan_response->body->name . ':' . $plan_response->body->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paypal_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Create Paypal subscription */
                $subscription_response = \Unirest\Request::post(
                    $paypal_api_url . 'v1/billing/subscriptions',
                    $paypal_headers,
                    \Unirest\Request\Body::json([
                        'plan_id' => $plan_response->body->id,
                        'start_time' => (new \DateTime())->modify('+' . $payment_frequency_days . ' days')->format(DATE_ISO8601),
                        'quantity' => 1,
                        'custom_id' => $paypal_custom_id,
                        'payment_method' => [
                            'payer_selected' => 'PAYPAL',
                            'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                        ],
                        'application_context' => [
                            'brand_name' => settings()->business->brand_name ?: settings()->main->title,
                            'shipping_preference' => 'NO_SHIPPING',
                            'user_action' => 'SUBSCRIBE_NOW',
                            'return_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                            'cancel_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount))
                        ]
                    ])
                );

                if($subscription_response->code >= 400) {
                    $paypal_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $subscription_response->body->name . ':' . $subscription_response->body->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paypal_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                $paypal_payment_url = $subscription_response->body->links[0]->href;
                header('Location: ' . $paypal_payment_url); die();

                break;
        }

    }

    private function stripe() {

        /* Initiate Stripe configuration */
        \Stripe\Stripe::setApiKey(settings()->stripe->secret_key);
        \Stripe\Stripe::setApiVersion('2023-10-16');

        /* Trial */
        $trial_days = 0;
        if($this->plan->trial_days && !$this->user->plan_trial_done && !isset($_GET['trial_skip'])) {
            $trial_days = $this->plan->trial_days;
        }

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price for Stripe amount */
        $stripe_formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '') * 100;

        /* Prepare formatted price string for URLs */
        $formatted_price = number_format($price_with_taxes, 2, '.', '');

        /* Prepare metadata for tracking */
        $stripe_metadata = [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'payment_frequency' => $_POST['payment_frequency'],
            'base_amount' => $base_amount,
            'code' => $code,
            'discount_amount' => $discount_amount,
            'taxes_ids' => json_encode($this->applied_taxes_ids)
        ];

        /* Prepare line item for payment/session */
        $stripe_line_item = [
            'price_data' => [
                'currency' => currency(),
                'product_data' => [
                    'name' => settings()->business->brand_name . ' - ' . $this->plan->name,
                    'description' => l('plan.custom_plan.' . $_POST['payment_frequency']),
                ],
                'unit_amount' => $stripe_formatted_price
            ],
            'quantity' => 1
        ];

        /* Add recurring interval for subscription if needed */
        if($_POST['payment_type'] === 'recurring') {
            $stripe_line_item['price_data']['recurring'] = [
                'interval' => 'day',
                'interval_count' => $payment_frequency_days
            ];
        }

        /* Build the Stripe session payload */
        $stripe_session_data = [
            'mode' => $_POST['payment_type'] === 'recurring' ? 'subscription' : 'payment',
            'customer_email' => $this->user->email,
            'currency' => currency(),
            'line_items' => [ $stripe_line_item ],
            'metadata' => $stripe_metadata,
            'success_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
            'cancel_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount)),
        ];

        if(settings()->stripe->automatic_tax_is_enabled) {
            $stripe_session_data['automatic_tax'] = [
                'enabled' => true,
            ];
        }

        /* Add subscription data if payment is recurring */
        if($_POST['payment_type'] === 'recurring') {
            $stripe_session_data['subscription_data'] = [
                'metadata' => $stripe_metadata
            ];

            /* Apply trial days when requested */
            if($trial_days > 0) {
                /* Collects a payment method now, first charge happens after trial ends */
                $stripe_session_data['subscription_data']['trial_period_days'] = $trial_days;
            }
        }

        /* Generate and redirect to Stripe session */
        try {
            $stripe_session = \Stripe\Checkout\Session::create($stripe_session_data);
        } catch (\Exception $exception) {

            /* Prepare redirect query parameters */
            $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
            $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

            $stripe_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($stripe_error_message);

            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Redirect user to Stripe checkout */
        header('Location: ' . $stripe_session->url); die();

    }

    private function parse_offline_payment_confirmation($text, $expected_amount = null) {
        $text = trim($text ?? '');
        if(empty($text)) {
            return [
                'is_valid' => false,
                'code' => null,
                'amount' => null,
                'datetime' => null,
                'raw_text' => null,
            ];
        }

        $result = [
            'is_valid' => false,
            'code' => null,
            'amount' => null,
            'datetime' => null,
            'raw_text' => $text,
        ];

        /* 1. Transaction Code (M-PESA / Bank / Airtel) */
        if(preg_match('/\b([A-Z0-9]{10})\s+(?:Confirmed|confirmed)/i', $text, $match)) {
            $result['code'] = strtoupper($match[1]);
        } else if(preg_match('/(?:Trans(?:action)?\s*(?:ID|Id|id|No|Ref)?|Ref(?:erence)?)\s*[:#.]?\s*([A-Z0-9]{6,20})/i', $text, $match)) {
            $result['code'] = strtoupper($match[1]);
        } else if(preg_match('/\b([A-Z]{2}[0-9A-Z]{8})\b/i', $text, $match)) {
            $result['code'] = strtoupper($match[1]);
        } else if(preg_match('/^([A-Z0-9]{8,14})\b/i', $text, $match)) {
            $result['code'] = strtoupper($match[1]);
        }

        /* 2. Amount */
        if(preg_match('/(?:Ksh|KES|\$|USD)\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?|[0-9]+(?:\.[0-9]{2})?)/i', $text, $match)) {
            $result['amount'] = (float) str_replace(',', '', $match[1]);
        } else if(preg_match('/(?:sent|paid|received|transferred)\s*(?:to|from)?\s*(?:Ksh|KES|\$)?\s*([0-9]{1,3}(?:,[0-9]{3})*(?:\.[0-9]{2})?|[0-9]+(?:\.[0-9]{2})?)/i', $text, $match)) {
            $result['amount'] = (float) str_replace(',', '', $match[1]);
        }

        /* 3. Date and Time Extraction */
        if(preg_match('/(?:on|On|dated|date:?)\s*(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})(?:\s+at|\s*,|\s+)\s*(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)/i', $text, $match)) {
            $day = (int) $match[1];
            $month = (int) $match[2];
            $year = (int) $match[3];
            $time = $match[4];

            if($year < 100) $year += 2000;
            if($month > 12 && $day <= 12) {
                $tmp = $day;
                $day = $month;
                $month = $tmp;
            }

            $formatted = sprintf('%04d-%02d-%02d %s', $year, $month, $day, $time);
            $timestamp = strtotime($formatted);
            if($timestamp) {
                $result['datetime'] = date('Y-m-d H:i:s', $timestamp);
            }
        } else if(preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})\s+at\s+(\d{1,2}:\d{2}(?::\d{2})?\s*(?:AM|PM|am|pm)?)/i', $text, $match)) {
            $day = (int) $match[1];
            $month = (int) $match[2];
            $year = (int) $match[3];
            $time = $match[4];

            if($year < 100) $year += 2000;
            if($month > 12 && $day <= 12) {
                $tmp = $day;
                $day = $month;
                $month = $tmp;
            }

            $formatted = sprintf('%04d-%02d-%02d %s', $year, $month, $day, $time);
            $timestamp = strtotime($formatted);
            if($timestamp) {
                $result['datetime'] = date('Y-m-d H:i:s', $timestamp);
            }
        } else if(preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/i', $text, $match)) {
            $day = (int) $match[1];
            $month = (int) $match[2];
            $year = (int) $match[3];
            if($year < 100) $year += 2000;
            if($month > 12 && $day <= 12) {
                $tmp = $day;
                $day = $month;
                $month = $tmp;
            }
            $result['datetime'] = sprintf('%04d-%02d-%02d 12:00:00', $year, $month, $day);
        }

        /* 4. Determine if valid for automatic activation */
        $has_code = !empty($result['code']);
        $has_date = !empty($result['datetime']);
        $amount_matches = true;

        if($expected_amount !== null && (float) $expected_amount > 0) {
            if($result['amount'] !== null) {
                $amount_matches = ($result['amount'] >= ((float) $expected_amount - 1.0));
            } else {
                $amount_matches = false;
            }
        }

        if($has_code && $has_date && $amount_matches) {
            $result['is_valid'] = true;
        }

        return $result;
    }

    private function offline_payment() {

        /* Redirect to thank you page if returning from a successful offline payment */
        if($this->return_type && $this->payment_processor === 'offline_payment' && $this->return_type === 'success') {
            $this->redirect_pay_thank_you();
        }

        /* Get price details */
        extract($this->get_price_details());

        /* Calculate and format the final price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare unique payment id */
        $payment_unique_id = md5(
            $this->user->user_id .
            $this->plan_id .
            $_POST['payment_type'] .
            $_POST['payment_frequency'] .
            $this->user->email .
            get_date()
        );

        /* Check if confirmation text was pasted */
        $offline_payment_proof_text = isset($_POST['offline_payment_proof_text']) ? trim($_POST['offline_payment_proof_text']) : '';

        /* Read text from uploaded text file if proof text is empty */
        if(empty($offline_payment_proof_text) && !empty($_FILES['offline_payment_proof']['name']) && !empty($_FILES['offline_payment_proof']['tmp_name'])) {
            $uploaded_ext = pathinfo($_FILES['offline_payment_proof']['name'], PATHINFO_EXTENSION);
            if(strtolower($uploaded_ext) === 'txt') {
                $offline_payment_proof_text = trim(file_get_contents($_FILES['offline_payment_proof']['tmp_name']));
            }
        }

        /* Check if offline payment proof (file or text) was provided */
        $offline_payment_proof_provided = !empty($_FILES['offline_payment_proof']['name']) || !empty($offline_payment_proof_text);

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Validate uploaded offline payment proof */
        if(!$offline_payment_proof_provided) {
            Alerts::add_error(l('pay.error_message.offline_payment_proof_missing'));
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Parse confirmation message for payment info, date, amount, and reference code */
        $parsed_payment = $this->parse_offline_payment_confirmation($offline_payment_proof_text, $price_with_taxes);

        /* Check if transaction code has already been used in an approved payment */
        if(!empty($parsed_payment['code'])) {
            $is_already_approved = db()->where('payment_id', $parsed_payment['code'])->where('status', 1)->has('payments');
            if($is_already_approved) {
                $parsed_payment['is_valid'] = false;
            }
        }

        $payment_datetime = $parsed_payment['datetime'] ?: get_date();
        $external_payment_id = $parsed_payment['code'] ?: $payment_unique_id;

        /* Handle file upload for offline payment proof or save text receipt */
        $offline_payment_proof_file = null;
        if(!empty($_FILES['offline_payment_proof']['name'])) {
            $offline_payment_proof_file = \Altum\Uploads::process_upload(
                null,
                'offline_payment_proofs',
                'offline_payment_proof',
                'offline_payment_proof_remove',
                settings()->offline_payment->proof_size_limit
            );
        } else if(!empty($offline_payment_proof_text)) {
            $txt_filename = md5($payment_unique_id . microtime()) . '.txt';
            $txt_content = "=== PAYMENT CONFIRMATION RECEIPT ===" . PHP_EOL;
            if(!empty($parsed_payment['code'])) $txt_content .= "Transaction Ref: " . $parsed_payment['code'] . PHP_EOL;
            if(!empty($parsed_payment['amount'])) $txt_content .= "Amount: " . $parsed_payment['amount'] . " " . currency() . PHP_EOL;
            if(!empty($parsed_payment['datetime'])) $txt_content .= "Extracted Payment Date: " . $parsed_payment['datetime'] . PHP_EOL;
            $txt_content .= "Auto-Activated: " . ($parsed_payment['is_valid'] ? 'YES' : 'NO') . PHP_EOL;
            $txt_content .= PHP_EOL . "=== CONFIRMATION MESSAGE ===" . PHP_EOL;
            $txt_content .= $offline_payment_proof_text . PHP_EOL;

            $proof_dir = \Altum\Uploads::get_full_path('offline_payment_proofs');
            if(!is_dir($proof_dir)) {
                @mkdir($proof_dir, 0755, true);
            }
            if(!file_exists($proof_dir . 'index.php')) {
                @file_put_contents($proof_dir . 'index.php', "<?php header('Location: /', true, 301); exit();");
            }
            file_put_contents($proof_dir . $txt_filename, $txt_content);
            $offline_payment_proof_file = $txt_filename;
        }

        /* If there are field errors, redirect back */
        if(Alerts::has_field_errors() && !Alerts::has_errors()) {
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        $plan = db()->where('plan_id', $this->plan_id)->getOne('plans', ['plan_id', 'name', 'settings']);

        $is_auto_activated = !empty($parsed_payment['is_valid']);
        $payment_status = $is_auto_activated ? 1 : 0;

        /* Insert the payment record into the database */
        $payment_id = db()->insert('payments', [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'processor' => 'offline_payment',
            'type' => $_POST['payment_type'],
            'frequency' => $_POST['payment_frequency'],
            'code' => $code,
            'discount_amount' => $discount_amount,
            'base_amount' => $base_amount,
            'email' => $this->user->email,
            'payment_id' => $external_payment_id,
            'name' => $this->user->name,
            'plan' => json_encode($plan),
            'billing' => settings()->payment->taxes_and_billing_is_enabled && $this->user->billing ? json_encode($this->user->billing) : null,
            'business' => json_encode(settings()->business),
            'taxes_ids' => !empty($this->applied_taxes_ids) ? json_encode($this->applied_taxes_ids) : null,
            'total_amount' => $formatted_price,
            'currency' => currency(),
            'payment_proof' => $offline_payment_proof_file,
            'status' => $payment_status,
            'datetime' => $payment_datetime
        ]);

        /* Process automatic account activation if valid payment info was extracted */
        if($is_auto_activated) {
            $current_plan_expiration_date = $this->plan_id == $this->user->plan_id ? $this->user->plan_expiration_date : '';
            $subscription_model = new \Altum\Models\Subscription();
            $renewal_period = $subscription_model->calculate_renewal_period($current_plan_expiration_date, $_POST['payment_frequency'], $payment_datetime);
            $plan_expiration_date = $renewal_period['period_end'];

            /* Update user record with active plan */
            db()->where('user_id', $this->user->user_id)->update('users', [
                'plan_id' => $this->plan_id,
                'plan_settings' => json_encode($plan->settings),
                'plan_expiration_date' => $plan_expiration_date,
                'plan_expiry_reminder' => 0,
                'plan_trial_done' => 1,
                'payment_processor' => 'offline_payment',
                'payment_total_amount' => $formatted_price,
                'payment_currency' => currency(),
            ]);

            /* Clear user cache */
            cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

            /* Sync subscription and generate paid invoice */
            try {
                $subscription_model->sync_from_successful_payment([
                    'local_payment_id' => $payment_id,
                    'external_payment_id' => $external_payment_id,
                    'payment_processor' => 'offline_payment',
                    'payment_total' => $formatted_price,
                    'total_amount_default_currency' => $formatted_price,
                    'payment_currency' => currency(),
                    'user_id' => $this->user->user_id,
                    'plan_id' => $this->plan_id,
                    'plan_name' => $plan->name,
                    'payment_frequency' => $_POST['payment_frequency'],
                    'code' => $code ?: null,
                    'discount_amount' => $discount_amount,
                    'base_amount' => $base_amount,
                    'taxes_ids' => !empty($this->applied_taxes_ids) ? json_encode($this->applied_taxes_ids) : null,
                    'payment_type' => $_POST['payment_type'],
                    'payment_subscription_id' => null,
                    'payment_datetime' => $payment_datetime,
                    'period_start' => $renewal_period['period_start'],
                    'period_end' => $plan_expiration_date,
                    'billing' => settings()->payment->taxes_and_billing_is_enabled && $this->user->billing ? json_encode($this->user->billing) : null,
                    'business' => json_encode(settings()->business),
                    'invoice_id' => !empty($_REQUEST['invoice_id']) ? (int) $_REQUEST['invoice_id'] : null,
                ]);
            } catch(\Exception $exception) {
                if(DEBUG) error_log($exception->getMessage());
            }

            /* Update proforma invoice if paying for an existing proforma */
            $proforma_invoice_id = isset($_REQUEST['invoice_id']) ? (int) $_REQUEST['invoice_id'] : null;
            if($proforma_invoice_id) {
                $subscription_model->mark_proforma_invoice_as_paid($proforma_invoice_id, [
                    'local_payment_id' => $payment_id,
                    'external_payment_id' => $external_payment_id,
                    'payment_processor' => 'offline_payment',
                    'payment_total' => $formatted_price,
                    'payment_currency' => currency(),
                    'payment_datetime' => $payment_datetime,
                    'user_id' => $this->user->user_id,
                    'plan_id' => $this->plan_id,
                    'plan_name' => $plan->name,
                    'payment_frequency' => $_POST['payment_frequency'],
                    'period_start' => $renewal_period['period_start'],
                    'period_end' => $plan_expiration_date,
                ]);
            }

            /* Send user confirmation email */
            $email_template = get_email_template(
                [],
                l('global.emails.user_payment.subject'),
                [
                    '{{NAME}}' => $this->user->name,
                    '{{PLAN_NAME}}' => $plan->name,
                    '{{PLAN_EXPIRATION_DATE}}' => \Altum\Date::get($plan_expiration_date, 2),
                    '{{USER_PLAN_LINK}}' => url('account-plan'),
                    '{{USER_PAYMENTS_LINK}}' => url('account-payments'),
                ],
                l('global.emails.user_payment.body')
            );
            send_mail($this->user->email, $email_template->subject, $email_template->body, ['anti_phishing_code' => $this->user->anti_phishing_code, 'language' => $this->user->language]);

            /* Check affiliate */
            (new \Altum\Models\Payments())->affiliate_payment_check($payment_id, $formatted_price, settings()->payment->default_currency, $this->user);

            /* Set friendly success message */
            Alerts::add_success(sprintf(
                'Payment verified! Confirmation code %s verified (Paid on %s). Your %s plan has been activated automatically until %s.',
                $external_payment_id,
                \Altum\Date::get($payment_datetime, 2),
                $plan->name,
                \Altum\Date::get($plan_expiration_date, 2)
            ));
        }

        /* Send admin notification if enabled */
        if(settings()->email_notifications->new_payment && !empty(settings()->email_notifications->emails)) {
            $payment_status_label = $is_auto_activated 
                ? (l('account_payments.status_approved') . ' (Auto-activated via confirmation message: ' . $external_payment_id . ')')
                : l('account_payments.status_pending');

            $email_template = get_email_template(
                [
                    '{{PROCESSOR}}' => l('pay.custom_plan.offline_payment'),
                    '{{TOTAL_AMOUNT}}' => $formatted_price,
                    '{{CURRENCY}}' => currency()
                ],
                l('global.emails.admin_new_payment_notification.subject'),
                [
                    '{{PROCESSOR}}' => l('pay.custom_plan.offline_payment'),
                    '{{TOTAL_AMOUNT}}' => $formatted_price,
                    '{{CURRENCY}}' => currency(),
                    '{{NAME}}' => $this->user->name,
                    '{{EMAIL}}' => $this->user->email,
                    '{{PLAN_NAME}}' => $plan->name,
                    '{{PAYMENT_FREQUENCY}}' => l('plan.custom_plan.' . $_POST['payment_frequency']),
                    '{{PAYMENT_TYPE}}' => l('pay.custom_plan.' . $_POST['payment_type'] . '_type'),
                    '{{PAYMENT_ID}}' => $payment_id,
                    '{{EXTERNAL_PAYMENT_ID}}' => $external_payment_id,
                    '{{PAYMENT_LINK}}' => url('admin/payments?id=' . $payment_id),
                    '{{DATE}}' => $payment_datetime,
                    '{{DATE_TIMEZONE}}' => \Altum\Date::$default_timezone,
                    '{{CODE}}' => $code ?: l('global.none'),
                    '{{DISCOUNT_AMOUNT}}' => $discount_amount,
                    '{{PAYMENT_STATUS}}' => $payment_status_label,
                ],
                l('global.emails.admin_new_payment_notification.body')
            );

            send_mail(
                explode(',', settings()->email_notifications->emails),
                $email_template->subject,
                $email_template->body
            );
        }

        if($is_auto_activated) {
            redirect('account-plan');
        } else {
            /* Redirect to the thank you page after offline payment submission */
            redirect('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount));
        }
    }

    private function payu() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format final price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Configure PayU environment and credentials */
        \OpenPayU_Configuration::setEnvironment(settings()->payu->mode);
        \OpenPayU_Configuration::setMerchantPosId(settings()->payu->merchant_pos_id);
        \OpenPayU_Configuration::setSignatureKey(settings()->payu->signature_key);
        \OpenPayU_Configuration::setOauthClientId(settings()->payu->oauth_client_id);
        \OpenPayU_Configuration::setOauthClientSecret(settings()->payu->oauth_client_secret);
        \OpenPayU_Configuration::setOauthTokenCache(new \OauthCacheFile(UPLOADS_PATH . 'cache'));

        /* Generate unique payment id */
        $payu_payment_id = md5(
            $this->user->user_id .
            $this->plan_id .
            $_POST['payment_type'] .
            $_POST['payment_frequency'] .
            $this->user->email .
            get_date()
        );

        /* Prepare PayU order data */
        $payu_order_data = [
            'notifyUrl' => SITE_URL . 'webhook-payu',
            'continueUrl' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
            'customerIp' => get_ip(),
            'merchantPosId' => \OpenPayU_Configuration::getOauthClientId() ?: \OpenPayU_Configuration::getMerchantPosId(),
            'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
            'currencyCode' => currency(),
            'totalAmount' => $formatted_price * 100,
            'extOrderId' => $payu_payment_id,
            'products' => [
                [
                    'name' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                    'unitPrice' => $formatted_price * 100,
                    'quantity' => 1
                ]
            ],
            'buyer' => [
                'email' => $this->user->email,
                'firstName' => $this->user->name
            ]
        ];

        try {
            $payu_response = \OpenPayU_Order::create($payu_order_data);
            $payu_status_description = \OpenPayU_Util::statusDesc($payu_response->getStatus());

            if($payu_response->getStatus() !== 'SUCCESS') {
                $payu_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $payu_status_description : l('pay.error_message.failed_payment');
                Alerts::add_error($payu_error_message);
                redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
            }

            /* Log payment intent to database */
            db()->insert('payments', [
                'user_id' => $this->user->user_id,
                'plan_id' => $this->plan_id,
                'processor' => 'payu',
                'type' => $_POST['payment_type'],
                'frequency' => $_POST['payment_frequency'],
                'code' => $code,
                'discount_amount' => $discount_amount,
                'base_amount' => $base_amount,
                'email' => $this->user->email,
                'payment_id' => $payu_payment_id,
                'name' => $this->user->name,
                'plan' => json_encode(db()->where('plan_id', $this->plan_id)->getOne('plans', ['plan_id', 'name'])),
                'billing' => settings()->payment->taxes_and_billing_is_enabled && $this->user->billing ? json_encode($this->user->billing) : null,
                'business' => json_encode(settings()->business),
                'taxes_ids' => !empty($this->applied_taxes_ids) ? json_encode($this->applied_taxes_ids) : null,
                'total_amount' => $formatted_price,
                'currency' => currency(),
                'status' => 'pending',
                'datetime' => get_date()
            ]);

            /* Redirect to PayU payment page */
            header('Location: ' . $payu_response->getResponse()->redirectUri); die();

        } catch (\OpenPayU_Exception $exception) {
            $payu_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($payu_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }
    }

    private function iyzico() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Generate custom payment ID */
        $custom_payment_id = md5(
            $this->user->user_id .
            $this->plan_id .
            $_POST['payment_type'] .
            $_POST['payment_frequency'] .
            $this->user->email .
            get_date()
        );

        /* Log the payment intent */
        $payment_log_id = db()->insert('payments', [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'processor' => 'iyzico',
            'type' => $_POST['payment_type'],
            'frequency' => $_POST['payment_frequency'],
            'code' => $code,
            'discount_amount' => $discount_amount,
            'base_amount' => $base_amount,
            'email' => $this->user->email,
            'payment_id' => $custom_payment_id,
            'name' => $this->user->name,
            'plan' => json_encode(db()->where('plan_id', $this->plan_id)->getOne('plans', ['plan_id', 'name'])),
            'billing' => (settings()->payment->taxes_and_billing_is_enabled && $this->user->billing)
                ? json_encode($this->user->billing) : null,
            'business' => json_encode(settings()->business),
            'taxes_ids' => !empty($this->applied_taxes_ids) ? json_encode($this->applied_taxes_ids) : null,
            'total_amount' => $formatted_price,
            'currency' => currency(),
            'status' => 'pending',
            'datetime' => get_date()
        ]);

        /* Set up Iyzico API options */
        $iyzico_options = new \Iyzipay\Options();
        $iyzico_options->setApiKey(settings()->iyzico->api_key);
        $iyzico_options->setSecretKey(settings()->iyzico->secret_key);
        $iyzico_options->setBaseUrl(
            settings()->iyzico->mode === 'live'
                ? 'https://api.iyzipay.com'
                : 'https://sandbox-api.iyzipay.com'
        );

        /* Build the product creation request */
        $iyzico_product_request = new \Iyzipay\Request\Iyzilink\IyziLinkSaveProductRequest();
        $iyzico_product_request->setLocale(\Iyzipay\Model\Locale::EN);
        $iyzico_product_request->setConversationId($payment_log_id);
        $iyzico_product_request->setName(currency());
        $iyzico_product_request->setDescription(
            settings()->business->brand_name .
            ' - ' . $this->plan->name .
            ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency'])
        );
        $iyzico_product_request->setBase64EncodedImage('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
        $iyzico_product_request->setPrice($formatted_price);
        $iyzico_product_request->setCurrency(currency());
        $iyzico_product_request->setAddressIgnorable(false);
        $iyzico_product_request->setSoldLimit(1);
        $iyzico_product_request->setInstallmentRequest(false);

        /* Send product creation request to Iyzico */
        $iyzico_response = \Iyzipay\Model\Iyzilink\IyziLinkSaveProduct::create(
            $iyzico_product_request,
            $iyzico_options
        );

        if($iyzico_response->getStatus() !== 'success') {
            $iyzico_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $iyzico_response->getErrorCode() . ':' . $iyzico_response->getErrorMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($iyzico_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Redirect user to Iyzico payment page */
        header('Location: ' . $iyzico_response->getUrl()); die();
    }

    private function paystack() {

        /* Set Paystack secret key */
        Paystack::$secret_key = settings()->paystack->secret_key;

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price as string with two decimals */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        $user_phone = preg_replace('/\D+/', '', (string) ($this->user->billing->phone ?? ''));
        $paystack_channels = (currency() == 'KES') 
            ? ['card', 'mobile_money'] 
            : ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'];

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Prepare one-time payment initialization payload */
                $paystack_one_time_payload = [
                    'key' => settings()->paystack->public_key,
                    'email' => $this->user->email,
                    'first_name' => $this->user->name,
                    'amount' => (int) round($formatted_price * 100),
                    'currency' => currency(),
                    'channels' => $paystack_channels,
                    'metadata' => [
                        'user_id' => $this->user->user_id,
                        'plan_id' => $this->plan_id,
                        'payment_frequency' => $_POST['payment_frequency'],
                        'base_amount' => $base_amount,
                        'code' => $code,
                        'discount_amount' => $discount_amount,
                        'taxes_ids' => json_encode($this->applied_taxes_ids),
                        'invoice_id' => !empty($_REQUEST['invoice_id']) ? (int) $_REQUEST['invoice_id'] : null,
                    ],
                    'callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount))
                ];

                if(!empty($user_phone)) {
                    $paystack_one_time_payload['metadata']['phone'] = $user_phone;
                }

                /* Create one-time payment link */
                $paystack_response = \Unirest\Request::post(
                    Paystack::$api_url . 'transaction/initialize',
                    Paystack::get_headers(),
                    \Unirest\Request\Body::json($paystack_one_time_payload)
                );

                if(!$paystack_response->body->status) {
                    $paystack_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $paystack_response->body->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paystack_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $paystack_response->body->data->authorization_url); die();

            case 'recurring':

                /* Prepare Paystack plan payload */
                $paystack_plan_payload = [
                    'name' => $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                    'interval' => match ($_POST['payment_frequency']) {
                        'annual' => 'annually',
                        default => $_POST['payment_frequency']
                    },
                    'amount' => (int) round($formatted_price * 100),
                    'currency' => currency()
                ];

                /* Create Paystack plan */
                $paystack_plan_response = \Unirest\Request::post(
                    Paystack::$api_url . 'plan',
                    Paystack::get_headers(),
                    \Unirest\Request\Body::json($paystack_plan_payload)
                );

                /* If recurring plan fails on Paystack (e.g. mobile money not supported for recurring in KES), fallback to one-time payment */
                if(!$paystack_plan_response->body->status) {
                    $paystack_one_time_payload = [
                        'key' => settings()->paystack->public_key,
                        'email' => $this->user->email,
                        'first_name' => $this->user->name,
                        'amount' => (int) round($formatted_price * 100),
                        'currency' => currency(),
                        'channels' => $paystack_channels,
                        'metadata' => [
                            'user_id' => $this->user->user_id,
                            'plan_id' => $this->plan_id,
                            'payment_frequency' => $_POST['payment_frequency'],
                            'base_amount' => $base_amount,
                            'code' => $code,
                            'discount_amount' => $discount_amount,
                            'taxes_ids' => json_encode($this->applied_taxes_ids)
                        ],
                        'callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount))
                    ];

                    if(!empty($user_phone)) {
                        $paystack_one_time_payload['metadata']['phone'] = $user_phone;
                    }

                    $fallback_response = \Unirest\Request::post(
                        Paystack::$api_url . 'transaction/initialize',
                        Paystack::get_headers(),
                        \Unirest\Request\Body::json($paystack_one_time_payload)
                    );

                    if($fallback_response->body && $fallback_response->body->status) {
                        header('Location: ' . $fallback_response->body->data->authorization_url); die();
                    }

                    $paystack_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $paystack_plan_response->body->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paystack_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                $paystack_plan_code = $paystack_plan_response->body->data->plan_code;

                /* Prepare recurring payment initialization payload */
                $paystack_recurring_payload = [
                    'key' => settings()->paystack->public_key,
                    'email' => $this->user->email,
                    'first_name' => $this->user->name,
                    'currency' => currency(),
                    'amount' => (int) round($formatted_price * 100),
                    'plan' => $paystack_plan_code,
                    'channels' => $paystack_channels,
                    'metadata' => [
                        'user_id' => $this->user->user_id,
                        'plan_id' => $this->plan_id,
                        'payment_frequency' => $_POST['payment_frequency'],
                        'base_amount' => $base_amount,
                        'code' => $code,
                        'discount_amount' => $discount_amount,
                        'taxes_ids' => json_encode($this->applied_taxes_ids),
                        'invoice_id' => !empty($_REQUEST['invoice_id']) ? (int) $_REQUEST['invoice_id'] : null,
                    ],
                    'callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount))
                ];

                if(!empty($user_phone)) {
                    $paystack_recurring_payload['metadata']['phone'] = $user_phone;
                }

                /* Create recurring payment link */
                $paystack_recurring_response = \Unirest\Request::post(
                    Paystack::$api_url . 'transaction/initialize',
                    Paystack::get_headers(),
                    \Unirest\Request\Body::json($paystack_recurring_payload)
                );

                if(!$paystack_recurring_response->body->status) {
                    /* Fallback to one-time initialization so payment succeeds with card or M-Pesa */
                    unset($paystack_recurring_payload['plan']);
                    $retry_response = \Unirest\Request::post(
                        Paystack::$api_url . 'transaction/initialize',
                        Paystack::get_headers(),
                        \Unirest\Request\Body::json($paystack_recurring_payload)
                    );

                    if($retry_response->body && $retry_response->body->status) {
                        header('Location: ' . $retry_response->body->data->authorization_url); die();
                    }

                    $paystack_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $paystack_recurring_response->body->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paystack_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $paystack_recurring_response->body->data->authorization_url); die();
        }

        die();
    }

    private function razorpay() {

        /* Initialize Razorpay API */
        $razorpay_api = new \Razorpay\Api\Api(settings()->razorpay->key_id, settings()->razorpay->key_secret);

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');
		$razorpay_amount = in_array(currency(), get_zero_decimal_currencies_array()) ? (int) $formatted_price : (int) round($formatted_price * 100);

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Create one-time payment link */
                try {
                    $razorpay_payment_link_response = $razorpay_api->paymentLink->create([
                        'amount' => $razorpay_amount,
                        'currency' => currency(),
                        'accept_partial' => false,
                        'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'customer' => [
                            'name' => $this->user->name,
                            'email' => $this->user->email
                        ],
                        'notify' => [
                            'sms' => false,
                            'email' => false
                        ],
                        'reminder_enable' => false,
                        'notes' => [
                            'user_id' => $this->user->user_id,
                            'plan_id' => $this->plan_id,
                            'payment_frequency' => $_POST['payment_frequency'],
                            'base_amount' => $base_amount,
                            'code' => $code,
                            'discount_amount' => $discount_amount,
                            'taxes_ids' => json_encode($this->applied_taxes_ids)
                        ],
                        'callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                        'callback_method' => 'get'
                    ]);
                } catch (\Exception $exception) {
                    $razorpay_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($razorpay_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $razorpay_payment_link_response['short_url']); die();

            case 'recurring':

                /* Create Razorpay plan */
                try {
                    $razorpay_plan_response = $razorpay_api->plan->create([
                        'period' => 'daily',
                        'interval' => $payment_frequency_days,
                        'item' => [
                            'name' => $this->plan->name,
                            'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                            'amount' => $razorpay_amount,
                            'currency' => currency()
                        ]
                    ]);
                } catch (\Exception $exception) {
                    $razorpay_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($razorpay_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Define total_count for auto-expiration (10 years default) */
				$total_count = match ($_POST['payment_frequency']) {
					'monthly' => 120,
					'quarterly' => 40,
					'biannual' => 20,
					'annual' => 10,
					default => 120
				};

                /* Create Razorpay subscription */
                try {
                    $razorpay_subscription_response = $razorpay_api->subscription->create([
                        'plan_id' => $razorpay_plan_response['id'],
                        'quantity' => 1,
                        'total_count' => $total_count,
                        'notes' => [
                            'user_id' => $this->user->user_id,
                            'plan_id' => $this->plan_id,
                            'payment_frequency' => $_POST['payment_frequency'],
                            'base_amount' => $base_amount,
                            'code' => $code,
                            'discount_amount' => $discount_amount,
                            'taxes_ids' => json_encode($this->applied_taxes_ids)
                        ]
                    ]);
                } catch (\Exception $exception) {
                    $razorpay_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($razorpay_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $razorpay_subscription_response['short_url']); die();
        }

        die();
    }

    private function mollie() {

        /* Initialize Mollie client */
        $mollie_client = new \Mollie\Api\MollieApiClient();
        $mollie_client->setApiKey(settings()->mollie->api_key);

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price to two decimals */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Shared payment data for Mollie */
        $mollie_payment_data = [
            'amount' => [
                'currency' => currency(),
                'value' => $formatted_price,
            ],
            'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
            'metadata' => [
                'user_id' => $this->user->user_id,
                'plan_id' => $this->plan_id,
                'payment_frequency' => $_POST['payment_frequency'],
                'base_amount' => $base_amount,
                'code' => $code,
                'discount_amount' => $discount_amount,
                'taxes_ids' => json_encode($this->applied_taxes_ids)
            ],
            'redirectUrl' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
            'webhookUrl' => SITE_URL . 'webhook-mollie'
        ];

        switch ($_POST['payment_type']) {

            case 'one_time':

                try {
                    $mollie_payment = $mollie_client->payments->create($mollie_payment_data);
                } catch (\Exception $exception) {
                    $mollie_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($mollie_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $mollie_payment->getCheckoutUrl()); die();

            case 'recurring':

                /* Create Mollie customer for recurring payments */
                try {
                    $mollie_customer = $mollie_client->customers->create([
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ]);
                } catch (\Exception $exception) {
                    $mollie_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($mollie_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Create the first recurring payment */
                try {
                    $mollie_payment = $mollie_customer->createPayment(array_merge($mollie_payment_data, [
                        'sequenceType' => 'first'
                    ]));
                } catch (\Exception $exception) {
                    $mollie_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($mollie_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $mollie_payment->getCheckoutUrl()); die();
        }

        die();
    }

    private function crypto_com() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Convert price to smallest currency unit (integer) */
        $formatted_price = number_format($price_with_taxes, 2, '.', '') * 100;

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Authenticate Crypto.com API */
                \Unirest\Request::auth(settings()->crypto_com->secret_key, '');

                /* Prepare Crypto.com payment payload */
                $crypto_com_payment_payload = [
                    'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                    'amount' => $formatted_price,
                    'currency' => currency(),
                    'metadata' => json_encode([
                        'user_id' => $this->user->user_id,
                        'plan_id' => $this->plan_id,
                        'payment_frequency' => $_POST['payment_frequency'],
                        'base_amount' => $base_amount,
                        'code' => $code,
                        'discount_amount' => $discount_amount,
                        'taxes_ids' => $this->applied_taxes_ids
                    ]),
                    'return_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price / 100, $code, $discount_amount)),
                    'cancel_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price / 100, $code, $discount_amount))
                ];

                /* Make payment request to Crypto.com */
                $crypto_com_response = \Unirest\Request::post(
                    'https://pay.crypto.com/api/payments',
                    [],
                    \Unirest\Request\Body::Form($crypto_com_payment_payload)
                );

                /* Handle errors */
                if($crypto_com_response->code >= 400) {
                    $crypto_com_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $crypto_com_response->body->error->type . ':' . $crypto_com_response->body->error->error_message : l('pay.error_message.failed_payment');
                    Alerts::add_error($crypto_com_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Redirect to Crypto.com payment page */
                header('Location: ' . $crypto_com_response->body->payment_url); die();

        }
    }

    private function paddle() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format final price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Build passthrough string for Paddle tracking */
                $paddle_custom_id = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);

                /* Set logo image url for Paddle checkout */
                $paddle_image_url = settings()->main->{'logo_' . \Altum\ThemeStyle::get()} !== ''
                    ? settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'}
                    : '';

                /* Prepare Paddle pay link API payload */
                $paddle_pay_link_payload = [
                    'vendor_id' => settings()->paddle->vendor_id,
                    'vendor_auth_code' => settings()->paddle->api_key,
                    'title' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                    'webhook_url' => SITE_URL . 'webhook-paddle',
                    'prices' => [currency() . ':' . $formatted_price],
                    'customer_email' => $this->user->email,
                    'passthrough' => $paddle_custom_id,
                    'return_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                    'image_url' => $paddle_image_url,
                    'quantity_variable' => 0
                ];

                /* Request Paddle payment link */
                $paddle_response = \Unirest\Request::post(
                    \Altum\PaymentGateways\Paddle::get_api_url() . '2.0/product/generate_pay_link',
                    [],
                    \Unirest\Request\Body::Form($paddle_pay_link_payload)
                );

                /* Handle Paddle API failure */
                if(!$paddle_response->body->success) {
                    $paddle_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $paddle_response->body->error->code . ':' . $paddle_response->body->error->message : l('pay.error_message.failed_payment');
                    Alerts::add_error($paddle_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Store the generated payment link */
                $this->payment_extra_data = [
                    'payment_processor' => 'paddle',
                    'url' => $paddle_response->body->response->url
                ];

                break;
        }

    }

    private function paddle_billing() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Convert to Paddle minor units (cents) */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '') * 100;

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Paddle API setup */
        $paddle_api_url = 'https://' . (settings()->paddle_billing->mode == 'sandbox' ? 'sandbox-api' : 'api') . '.paddle.com/';
        $paddle_headers = [
            'Authorization' => 'Bearer ' . settings()->paddle_billing->api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        /* Prepare metadata for tracking */
        $metadata = [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'payment_frequency' => $_POST['payment_frequency'],
            'base_amount' => $base_amount,
            'code' => $code,
            'discount_amount' => $discount_amount,
            'taxes_ids' => json_encode($this->applied_taxes_ids),
            'payment_type' => $_POST['payment_type']
        ];

        /* Create Product */
        $product_payload = [
            'name' => settings()->business->brand_name . ' - ' . $this->plan->name,
            'description' => l('plan.custom_plan.' . $_POST['payment_frequency']),
            'tax_category' => 'standard'
        ];

        $paddle_product_response = \Unirest\Request::post(
            $paddle_api_url . 'products',
            $paddle_headers,
            \Unirest\Request\Body::json($product_payload)
        );

        if($paddle_product_response->code >= 400) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? print_r($paddle_product_response->body, true) : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        $paddle_product_id = $paddle_product_response->body->data->id;

        /* Create Price (one-time or recurring) */
        $price_payload = [
            'product_id' => $paddle_product_id,
            'name' => ucfirst($_POST['payment_type']) . ' - ' . $this->plan->name,
            'description' => l('plan.custom_plan.' . $_POST['payment_frequency']),
            'unit_price' => [
                'amount' => (string) $formatted_price,
                'currency_code' => currency()
            ],
            'quantity' => [
                'minimum' => 1,
                'maximum' => 1
            ],
            'tax_mode' => 'account_setting',
            'custom_data' => $metadata
        ];

        if($_POST['payment_type'] == 'recurring') {
            switch ($_POST['payment_frequency']) {
                case 'monthly':
                    $interval = 'month';
                    $frequency = 1;
                    break;

                case 'quarterly':
                    $interval = 'month';
                    $frequency = 3;
                    break;

                case 'biannual':
                    $interval = 'month';
                    $frequency = 6;
                    break;

                case 'annual':
                    $interval = 'year';
                    $frequency = 1;
                    break;
            };


            $price_payload['billing_cycle'] = [
                'interval' => $interval,
                'frequency' => $frequency,
            ];
        }

        $paddle_price_response = \Unirest\Request::post(
            $paddle_api_url . 'prices',
            $paddle_headers,
            \Unirest\Request\Body::json($price_payload)
        );

        if($paddle_price_response->code >= 400) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? print_r($paddle_price_response->body, true) : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        $paddle_price_id = $paddle_price_response->body->data->id;

        /* Pass data to view (for overlay checkout) */
        $this->payment_extra_data = [
            'payment_processor' => 'paddle_billing',
            'price_id' => $paddle_price_id,
            'success_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
        ];
    }

    private function yookassa() {

        /* Initialize YooKassa client */
        $yookassa_client = new \YooKassa\Client();
        $yookassa_client->setAuth(settings()->yookassa->shop_id, settings()->yookassa->secret_key);

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price to string with two decimals */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                try {
                    /* Prepare payment metadata */
                    $yookassa_payment_metadata = [
                        'user_id' => $this->user->user_id,
                        'plan_id' => $this->plan_id,
                        'payment_frequency' => $_POST['payment_frequency'],
                        'base_amount' => $base_amount,
                        'code' => $code,
                        'discount_amount' => $discount_amount,
                        'taxes_ids' => json_encode($this->applied_taxes_ids)
                    ];

                    /* Prepare payment receipt item */
                    $yookassa_receipt_item = [
                        'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'quantity' => 1,
                        'amount' => [
                            'currency' => currency(),
                            'value' => $formatted_price
                        ],
                        'vat_code' => 1,
                        'payment_subject' => 'commodity',
                        'payment_mode' => 'full_payment'
                    ];

                    /* Prepare payment request payload */
                    $yookassa_payment_payload = [
                        'amount' => [
                            'currency' => currency(),
                            'value' => $formatted_price
                        ],
                        'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'metadata' => $yookassa_payment_metadata,
                        'receipt' => [
                            'customer' => [
                                'email' => $this->user->email
                            ],
                            'items' => [
                                $yookassa_receipt_item
                            ]
                        ],
                        'confirmation' => [
                            'type' => 'redirect',
                            'return_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount))
                        ],
                        'capture' => true
                    ];

                    /* Create YooKassa payment */
                    $yookassa_payment = $yookassa_client->createPayment(
                        $yookassa_payment_payload,
                        uniqid('', true)
                    );

                } catch (\Exception $exception) {
                    $yookassa_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
                    Alerts::add_error($yookassa_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $yookassa_payment->getConfirmation()->getConfirmationUrl()); die();
        }

        die();
    }

    private function mercadopago() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Build unique custom id */
                $mercadopago_custom_id = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);

                /* Build MercadoPago payload */
                $mercadopago_payload = [
                    'items' => [[
                        'id' => $this->plan->plan_id,
                        'title' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                        'quantity' => 1,
                        'currency_id' => currency(),
                        'unit_price' => (float) $formatted_price
                    ]],
                    'back_urls' => [
                        'success' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                        'pending' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                        'failure' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount))
                    ],
                    'external_reference' => $mercadopago_custom_id,
                    'notification_url' => SITE_URL . 'webhook-mercadopago'
                ];

                $mercadopago_response = \Unirest\Request::post(
                    'https://api.mercadopago.com/checkout/preferences',
                    [
                        'Authorization' => 'Bearer ' . settings()->mercadopago->access_token,
                        'Content-Type' => 'application/json'
                    ],
                    \Unirest\Request\Body::json($mercadopago_payload)
                );

                /* Handle MercadoPago errors */
                if($mercadopago_response->code >= 400) {
                    $mercadopago_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $mercadopago_response->body->error . ':' . $mercadopago_response->body->message : l('pay.error_message.failed_payment');

                    Alerts::add_error($mercadopago_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Redirect to MercadoPago checkout */
                header('Location: ' . $mercadopago_response->body->init_point); die();

                break;
        }
    }

    private function midtrans() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price as integer (Midtrans requirement) */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Generate custom identifiers for tracking */
                $midtrans_custom_id = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);
                $midtrans_payment_id = md5($this->user->user_id . $this->plan_id . $_POST['payment_type'] . $_POST['payment_frequency'] . $this->user->email . get_date());

                /* Build Midtrans API URL */
                $midtrans_api_url = 'https://api' . (settings()->midtrans->mode == 'sandbox' ? 'sandbox' : '') . '.midtrans.com/v1/payment-links';

                /* Prepare API headers */
                $midtrans_headers = [
                    'Authorization' => 'Basic ' . base64_encode(settings()->midtrans->server_key . ':'),
                    'Content-Type' => 'application/json',
                    'X-Override-Notification' => SITE_URL . 'webhook-midtrans'
                ];

                /* Prepare API request body */
                $midtrans_body = [
                    'transaction_details' => [
                        'order_id' => md5($midtrans_payment_id),
                        'gross_amount' => $formatted_price
                    ],
                    'expiry' => [
                        'duration' => 1,
                        'unit' => 'days'
                    ],
                    'item_details' => [[
                        'price' => $formatted_price,
                        'quantity' => 1,
                        'name' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency'])
                    ]],
                    'custom_field1' => $midtrans_custom_id
                ];

                /* Send request to Midtrans */
                $midtrans_response = \Unirest\Request::post(
                    $midtrans_api_url,
                    $midtrans_headers,
                    \Unirest\Request\Body::json($midtrans_body)
                );

                /* Handle errors */
                if($midtrans_response->code >= 400) {
                    $midtrans_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $midtrans_response->body->error_messages : l('pay.error_message.failed_payment');

                    Alerts::add_error($midtrans_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                /* Redirect to Midtrans payment URL */
                header('Location: ' . $midtrans_response->body->payment_url); die();

        }
    }

    private function flutterwave() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price as integer for API */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        $customer_name = !empty($this->user->name) ? $this->user->name : explode('@', $this->user->email)[0];
        $customer_phone = !empty($this->user->billing->phone) ? $this->user->billing->phone : '';

        /* Generate unique payment id */
        $payment_id = md5(
            $this->user->user_id .
            $this->plan_id .
            $_POST['payment_type'] .
            $_POST['payment_frequency'] .
            $this->user->email .
            get_date()
        );

        $flutterwave_base_payload = [
            'tx_ref' => $payment_id,
            'amount' => $formatted_price,
            'currency' => currency(),
            'redirect_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
            'meta' => [
                'user_id' => $this->user->user_id,
                'plan_id' => $this->plan_id,
                'payment_frequency' => $_POST['payment_frequency'],
                'base_amount' => $base_amount,
                'code' => $code,
                'discount_amount' => $discount_amount,
                'taxes_ids' => json_encode($this->applied_taxes_ids)
            ],
            'customer' => [
                'email' => $this->user->email,
                'name' => $customer_name,
                'phonenumber' => $customer_phone
            ],
            'customizations' => [
                'title' => settings()->business->brand_name ?: 'SeamlessQRCode',
                'description' => $this->plan->name . ' - ' . ucfirst($_POST['payment_frequency'])
            ]
        ];

        switch ($_POST['payment_type']) {

            case 'one_time':

                /* Make Flutterwave API request */
                $flutterwave_response = \Unirest\Request::post(
                    'https://api.flutterwave.com/v3/payments',
                    [
                        'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                        'Content-Type' => 'application/json',
                    ],
                    \Unirest\Request\Body::json($flutterwave_base_payload)
                );

                if($flutterwave_response->code >= 400 || empty($flutterwave_response->body->data->link)) {
                    $flutterwave_error_message = (DEBUG || \Altum\Authentication::is_admin()) 
                        ? ($flutterwave_response->body->message ?? l('pay.error_message.failed_payment')) 
                        : l('pay.error_message.failed_payment');
                    Alerts::add_error($flutterwave_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $flutterwave_response->body->data->link); die();

            case 'recurring':

                /* Match frequency names for Flutterwave */
                $flutterwave_payment_frequency = match ($_POST['payment_frequency']) {
                    'annual' => 'yearly',
                    'biannual' => 'bi-annually',
                    default => $_POST['payment_frequency']
                };

                $payment_plan = null;

                /* Try to fetch or create recurring plan */
                try {
                    $payment_plans_response = \Unirest\Request::get(
                        'https://api.flutterwave.com/v3/payment-plans',
                        [
                            'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                            'Content-Type' => 'application/json',
                        ]
                    );

                    if($payment_plans_response->code < 400 && !empty($payment_plans_response->body->data) && is_iterable($payment_plans_response->body->data)) {
                        foreach ($payment_plans_response->body->data as $existing_plan) {
                            if(
                                $existing_plan->amount == $formatted_price && 
                                $existing_plan->interval == $flutterwave_payment_frequency &&
                                (!isset($existing_plan->currency) || strtoupper($existing_plan->currency) == strtoupper(currency()))
                            ) {
                                $payment_plan = $existing_plan;
                                break;
                            }
                        }
                    }

                    if(!$payment_plan) {
                        $create_plan_response = \Unirest\Request::post(
                            'https://api.flutterwave.com/v3/payment-plans',
                            [
                                'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                                'Content-Type' => 'application/json',
                            ],
                            \Unirest\Request\Body::json([
                                'name' => (settings()->business->brand_name ?: 'Seamless') . ' - ' . $this->plan->name . ' (' . currency() . ')',
                                'amount' => $formatted_price,
                                'currency' => currency(),
                                'interval' => $flutterwave_payment_frequency
                            ])
                        );

                        if($create_plan_response->code < 400 && !empty($create_plan_response->body->data)) {
                            $payment_plan = $create_plan_response->body->data;
                        }
                    }
                } catch (\Exception $exception) {
                    $payment_plan = null;
                }

                $flutterwave_recurring_payload = $flutterwave_base_payload;
                if($payment_plan && !empty($payment_plan->id)) {
                    $flutterwave_recurring_payload['payment_plan'] = $payment_plan->id;
                }

                $flutterwave_recurring_response = \Unirest\Request::post(
                    'https://api.flutterwave.com/v3/payments',
                    [
                        'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                        'Content-Type' => 'application/json',
                    ],
                    \Unirest\Request\Body::json($flutterwave_recurring_payload)
                );

                /* If recurring payment plan request fails (e.g. Flutterwave rejects USD recurring or currency mismatch), fallback to standard payment link */
                if(($flutterwave_recurring_response->code >= 400 || empty($flutterwave_recurring_response->body->data->link)) && isset($flutterwave_recurring_payload['payment_plan'])) {
                    unset($flutterwave_recurring_payload['payment_plan']);

                    $flutterwave_recurring_response = \Unirest\Request::post(
                        'https://api.flutterwave.com/v3/payments',
                        [
                            'Authorization' => 'Bearer ' . settings()->flutterwave->secret_key,
                            'Content-Type' => 'application/json',
                        ],
                        \Unirest\Request\Body::json($flutterwave_recurring_payload)
                    );
                }

                if($flutterwave_recurring_response->code >= 400 || empty($flutterwave_recurring_response->body->data->link)) {
                    $flutterwave_error_message = (DEBUG || \Altum\Authentication::is_admin()) 
                        ? ($flutterwave_recurring_response->body->message ?? l('pay.error_message.failed_payment')) 
                        : l('pay.error_message.failed_payment');
                    Alerts::add_error($flutterwave_error_message);
                    redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
                }

                header('Location: ' . $flutterwave_recurring_response->body->data->link); die();
        }
    }

    private function lemonsqueezy() {

        /* Set Lemonsqueezy API key */
        Lemonsqueezy::$api_key = settings()->lemonsqueezy->api_key;

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '') * 100;

        /* Store string price for redirect parameters */
        $formatted_price_for_url = number_format($price_with_taxes, 2, '.', '');

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Prepare custom user data for checkout */
        $custom_user_data = [
            'user_id' => (string) $this->user->user_id,
            'plan_id' => (string) $this->plan_id,
            'payment_frequency' => (string) $_POST['payment_frequency'],
            'base_amount' => (string) $base_amount,
            'code' => (string) ($code ?: 'null'),
            'discount_amount' => (string) $discount_amount,
            'taxes_ids' => json_encode($this->applied_taxes_ids),
        ];

        /* Prepare Lemonsqueezy checkout payload */
        $lemonsqueezy_checkout_payload = [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'custom_price' => $formatted_price,
                    'expires_at' => (new \DateTime('now', new \DateTimeZone('UTC')))
                        ->add(new \DateInterval('P1D'))
                        ->format(\DateTime::ATOM),
                    'checkout_data' => [
                        'email' => $this->user->email,
                        'name' => $this->user->name,
                        'custom' => $custom_user_data,
                    ],
                    'product_options' => [
                        'redirect_url' => url(
                            'pay/' . $this->plan_id .
                            $this->return_url_parameters('success', $base_amount, $formatted_price_for_url, $code, $discount_amount)
                        )
                    ]
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => settings()->lemonsqueezy->store_id,
                        ]
                    ],
                    'variant' => [
                        'data' => [
                            'type' => 'variants',
                            'id' => settings()->lemonsqueezy->{$_POST['payment_type'] . '_' . $_POST['payment_frequency'] . '_variant_id'},
                        ]
                    ]
                ]
            ]
        ];

        /* Generate the payment link */
        $lemonsqueezy_response = \Unirest\Request::post(
            Lemonsqueezy::$api_url . 'checkouts',
            Lemonsqueezy::get_headers(),
            \Unirest\Request\Body::json($lemonsqueezy_checkout_payload)
        );

        if($lemonsqueezy_response->code === 201 && !empty($lemonsqueezy_response->body->data->attributes->url)) {
            /* Redirect to payment */
            header('Location: ' . $lemonsqueezy_response->body->data->attributes->url); die();
        } else {
            /* Handle payment creation errors */
            $lemonsqueezy_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? print_r($lemonsqueezy_response->body->errors, true) : l('pay.error_message.failed_payment');
            Alerts::add_error($lemonsqueezy_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

    }

    private function myfatoorah() {

        /* Get price details */
        extract($this->get_price_details());

        /* Apply taxes to base price */
        $price_with_taxes = $this->calculate_price_with_taxes($price);

        /* Format price as integer (no decimals for API) */
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Prepare meta data for customer reference */
        $customer_reference = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* Prepare MyFatoorah payload */
        $myfatoorah_payload = [
            'InvoiceValue' => $formatted_price,
            'CustomerName' => $this->user->name,
            'CustomerEmail' => $this->user->email,
            'WebhookUrl' => SITE_URL . 'webhook-myfatoorah',
            'NotificationOption' => 'LNK',
            'CustomerReference' => $customer_reference,
            'CallBackUrl' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
            'ErrorUrl' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount))
        ];

        /* Send payment request to MyFatoorah */
        try {
            $myfatoorah_response = \Unirest\Request::post(
                'https://' . settings()->myfatoorah->api_endpoint . '/v2/SendPayment',
                [
                    'Authorization' => 'Bearer ' . settings()->myfatoorah->api_key,
                    'Content-Type' => 'application/json'
                ],
                \Unirest\Request\Body::json($myfatoorah_payload)
            );
        } catch (\Exception $exception) {
            $myfatoorah_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($myfatoorah_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($myfatoorah_response->code === 200 && !empty($myfatoorah_response->body->Data->InvoiceURL)) {
            /* Redirect to payment */
            header('Location: ' . $myfatoorah_response->body->Data->InvoiceURL); die();
        } else {
            $myfatoorah_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? print_r($myfatoorah_response->body, true) : l('pay.error_message.failed_payment');
            Alerts::add_error($myfatoorah_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

    }

    private function klarna() {

        $klarna_base_url = settings()->klarna->mode;

        /* price */
        extract($this->get_price_details());
        $price_with_taxes = $this->calculate_price_with_taxes($price);
        $klarna_amount = round($price_with_taxes * 100);
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* metadata */
        $klarna_metadata = [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'payment_frequency' => $_POST['payment_frequency'],
            'base_amount' => $base_amount,
            'code' => $code,
            'discount_amount' => $discount_amount,
            'taxes_ids' => json_encode($this->applied_taxes_ids)
        ];

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        /* order lines */
        $klarna_order_lines = [
            [
                'type' => 'digital',
                'reference' => (string) $this->plan_id,
                'name' => settings()->business->brand_name . ' - ' . $this->plan->name,
                'quantity' => 1,
                'unit_price' => $klarna_amount,
                'total_amount' => $klarna_amount,
                'tax_rate' => 0,
                'total_tax_amount' => 0
            ]
        ];

        $purchase_country = $this->user->country;
        $purchase_currency = currency();
        $locale = 'en-US';

        /* session */
        $payments_payload = [
            'intent' => 'buy',
            'purchase_country' => $purchase_country,
            'purchase_currency' => $purchase_currency,
            'locale' => $locale,
            'order_amount' => $klarna_amount,
            'order_tax_amount' => 0,
            'order_lines' => $klarna_order_lines,
            'merchant_data' => json_encode($klarna_metadata),
            //'merchant_urls' => [
            //'push' => url('webhook-klarna?is_complete=1&session_id={session.id}&order_id={order.id}')
            //]
        ];

        try {
            $payments_response = \Unirest\Request::post(
                $klarna_base_url . '/payments/v1/sessions',
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(settings()->klarna->username . ':' . settings()->klarna->password)
                ],
                json_encode($payments_payload)
            );
        } catch (\Exception $exception) {
            $klarna_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($klarna_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($payments_response->code >= 400 || empty($payments_response->body->session_id)) {
            $klarna_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? json_encode($payments_response->body) : l('pay.error_message.failed_payment');
            Alerts::add_error($klarna_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        $payment_session_url = $klarna_base_url . '/payments/v1/sessions/' . $payments_response->body->session_id;

        /* HPP session */
        $hpp_payload = [
            'payment_session_url' => $payment_session_url,
            'place_order_mode' => 'CAPTURE_ORDER',
            'merchant_urls' => [
                'back' => url('pay/' . $this->plan_id),
                'cancel' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount)),
                'error' => url('pay/' . $this->plan_id . $this->return_url_parameters('error', $base_amount, $formatted_price, $code, $discount_amount)),
                'failure' => url('pay/' . $this->plan_id . $this->return_url_parameters('cancel', $base_amount, $formatted_price, $code, $discount_amount)),
                'status_update' => url('webhook-klarna'),
                'success' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount) . '&token={{authorization_token}}')
            ],
            'merchant_data' => json_encode($klarna_metadata)
        ];

        try {
            $hpp_response = \Unirest\Request::post(
                $klarna_base_url . '/hpp/v1/sessions',
                [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(settings()->klarna->username . ':' . settings()->klarna->password)
                ],
                json_encode($hpp_payload)
            );
        } catch (\Exception $exception) {
            $klarna_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($klarna_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($hpp_response->code >= 400 || empty($hpp_response->body->redirect_url)) {
            $klarna_error_message = (DEBUG || \Altum\Authentication::is_admin()) ? json_encode($hpp_response->body) : l('pay.error_message.failed_payment');
            Alerts::add_error($klarna_error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Log payment intent to database */
        db()->insert('payments', [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'processor' => 'klarna',
            'type' => $_POST['payment_type'],
            'frequency' => $_POST['payment_frequency'],
            'code' => $code,
            'discount_amount' => $discount_amount,
            'base_amount' => $base_amount,
            'email' => $this->user->email,
            'payment_id' => $hpp_response->body->session_id,
            'name' => $this->user->name,
            'plan' => json_encode(db()->where('plan_id', $this->plan_id)->getOne('plans', ['plan_id', 'name'])),
            'billing' => settings()->payment->taxes_and_billing_is_enabled && $this->user->billing ? json_encode($this->user->billing) : null,
            'business' => json_encode(settings()->business),
            'taxes_ids' => !empty($this->applied_taxes_ids) ? json_encode($this->applied_taxes_ids) : null,
            'total_amount' => $formatted_price,
            'currency' => currency(),
            'status' => 'pending',
            'datetime' => get_date()
        ]);

        header('Location: ' . $hpp_response->body->redirect_url);
        die();
    }

    private function plisio() {

        /* price */
        extract($this->get_price_details());
        $price_with_taxes = $this->calculate_price_with_taxes($price);
        $amount = $price_with_taxes;
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Id & Order */
        $order_name = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);
        $order_id = md5(uniqid('', true) . random_bytes(16));

        /* Crypto */
        $cryptocurrency = isset($_POST['cryptocurrency']) && in_array($_POST['cryptocurrency'], settings()->plisio->accepted_cryptocurrencies) ? $_POST['cryptocurrency'] : settings()->plisio->default_cryptocurrency;

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        try {
            $response = \Unirest\Request::get(
                Plisio::get_api_url() . 'api/v1/invoices/new?' . http_build_query([
                    'source_currency' => currency(),
                    'source_amount' => $amount,
                    'currency' => $cryptocurrency,
                    'email' => $this->user->email,
                    'order_name' => $order_name,
                    'order_number' => $order_id,
                    'callback_url' => SITE_URL . 'webhook-plisio',
                    'success_callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                    'fail_callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                    'api_key' => settings()->plisio->secret_key,
                ]),
            );
        } catch (\Exception $exception) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($response->code >= 400 || $response->body->status != 'success') {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? json_encode($response->body) : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        header('Location: ' . $response->body->data->invoice_url); die();
    }

    private function plisio_whitelabel() {

        /* Redirect to thank you page if returning from a successful offline payment */
        if($this->return_type && $this->payment_processor === 'plisio_whitelabel' && $this->return_type === 'success') {
            $this->redirect_pay_thank_you();
        }

        /* price */
        extract($this->get_price_details());
        $price_with_taxes = $this->calculate_price_with_taxes($price);
        $amount = $price_with_taxes;
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* Id & Order */
        $order_name = $this->user->user_id . '&' . $this->plan_id . '&' . $_POST['payment_frequency'] . '&' . $base_amount . '&' . $code . '&' . $discount_amount . '&' . json_encode($this->applied_taxes_ids);
        $order_id = md5(uniqid('', true) . random_bytes(16));

        /* Crypto */
        $cryptocurrency = isset($_POST['cryptocurrency']) && in_array($_POST['cryptocurrency'], settings()->plisio->accepted_cryptocurrencies) ? $_POST['cryptocurrency'] : settings()->plisio->default_cryptocurrency;

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        try {
            $response = \Unirest\Request::get(
                Plisio::get_api_url() . 'api/v1/invoices/new?' . http_build_query([
                    'source_currency' => currency(),
                    'source_amount' => $amount,
                    'currency' => $cryptocurrency,
                    'email' => $this->user->email,
                    'order_name' => $order_name,
                    'order_number' => $order_id,
                    'callback_url' => SITE_URL . 'webhook-plisio-whitelabel',
                    'success_callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                    'fail_callback_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount)),
                    'api_key' => settings()->plisio->secret_key,
                ]),
            );
        } catch (\Exception $exception) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($response->code >= 400 || $response->body->status != 'success') {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? json_encode($response->body) : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        /* Store the generated payment link */
        $this->payment_extra_data = [
            'payment_processor' => 'plisio_whitelabel',
            'wallet' => $response->body->data->wallet_hash,
            'qr_code' => $response->body->data->qr_code,
            'amount' => $response->body->data->amount,
            'cryptocurrency' => $response->body->data->currency,
            'expiration_timestamp' => $response->body->data->expire_utc,
            'success_url' => url('pay/' . $this->plan_id . $this->return_url_parameters('success', $base_amount, $formatted_price, $code, $discount_amount))
        ];

        error_log(print_r($this->payment_extra_data, true));
    }

    private function revolut() {

        /* price */
        extract($this->get_price_details());
        $price_with_taxes = $this->calculate_price_with_taxes($price);
        $amount = round($price_with_taxes * 100);
        $formatted_price = in_array(currency(), get_zero_decimal_currencies_array()) ? number_format($price_with_taxes, 0, '.', '') : number_format($price_with_taxes, 2, '.', '');

        /* metadata */
        $metadata = [
            'user_id' => $this->user->user_id,
            'plan_id' => $this->plan_id,
            'payment_frequency' => $_POST['payment_frequency'],
            'base_amount' => $base_amount,
            'code' => $code,
            'discount_amount' => $discount_amount,
            'taxes_ids' => json_encode($this->applied_taxes_ids)
        ];

        /* Prepare redirect query parameters */
        $trial_skip_parameter = isset($_GET['trial_skip']) ? '&trial_skip=true' : '';
        $discount_code_parameter = isset($_GET['code']) ? '&code=' . $_GET['code'] : '';

        try {
            $response = \Unirest\Request::post(
                Revolut::get_api_url() . 'api/orders',
                [
                    'Authorization' => 'Bearer ' . settings()->revolut->secret_key,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Revolut-Api-Version' => '2024-09-01',
                ],
                \Unirest\Request\Body::json([
                    'amount' => $formatted_price,
                    'currency' => currency(),
                    'description' => settings()->business->brand_name . ' - ' . $this->plan->name . ' - ' . l('plan.custom_plan.' . $_POST['payment_frequency']),
                    'customer' => [
                        'full_name' => $this->user->name,
                        'email' => $this->user->email,
                    ],
                    'metadata' => $metadata,
                ])
            );
        } catch (\Exception $exception) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? $exception->getMessage() : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        if($response->code >= 400) {
            $error_message = (DEBUG || \Altum\Authentication::is_admin()) ? json_encode($response->body) : l('pay.error_message.failed_payment');
            Alerts::add_error($error_message);
            redirect('pay/' . $this->plan_id . '?' . $trial_skip_parameter . $discount_code_parameter);
        }

        header('Location: ' . $response->body->checkout_url);
        die();
    }

}
