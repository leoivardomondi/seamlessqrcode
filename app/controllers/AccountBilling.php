<?php

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\Subscription;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class AccountBilling extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->payment->is_enabled) {
            redirect('not-found');
        }

        $subscription_model = new Subscription();
        $subscription_model->ensure_schema();

        $active_subscription = $subscription_model->get_active_by_user_id($this->user->user_id);

        $subscriptions = db()
            ->where('user_id', $this->user->user_id)
            ->orderBy('subscription_id', 'DESC')
            ->get('subscriptions', 25);

        $invoices = db()
            ->where('user_id', $this->user->user_id)
            ->orderBy('invoice_id', 'DESC')
            ->get('invoices', 25);

        $billing_events = db()
            ->where('user_id', $this->user->user_id)
            ->orderBy('billing_event_id', 'DESC')
            ->get('billing_events', 25);

        $entitlements = $active_subscription ? $subscription_model->get_entitlements($active_subscription->subscription_id) : $this->user->plan_settings;

        $payment_processors = require APP_PATH . 'includes/payment_processors.php';

        $menu = new \Altum\View('partials/account_header_menu', (array) $this);
        $this->add_view_content('account_header_menu', $menu->run());

        $data = [
            'active_subscription' => $active_subscription,
            'subscriptions' => $subscriptions,
            'invoices' => $invoices,
            'billing_events' => $billing_events,
            'entitlements' => $entitlements,
            'payment_processors' => $payment_processors,
        ];

        $view = new \Altum\View('account-billing/index', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }

    public function cancel_subscription() {

        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('account-billing');
        }

        $subscription_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $subscription_model = new Subscription();
        $subscription = $subscription_model->get_by_id($subscription_id);

        if(!$subscription || (int) $subscription->user_id !== (int) $this->user->user_id) {
            redirect('account-billing');
        }

        try {
            if($subscription->external_subscription_id && $this->user->payment_subscription_id == $subscription->external_subscription_id) {
                (new User())->cancel_subscription($this->user->user_id);
            } else {
                $subscription_model->cancel($subscription->subscription_id, 'customer_request', true);
            }

            Alerts::add_success(l('account_billing.success_message.subscription_canceled'));
        } catch(\Exception $exception) {
            Alerts::add_error($exception->getCode() . ':' . $exception->getMessage());
        }

        redirect('account-billing');
    }

    public function pause_subscription() {

        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('account-billing');
        }

        $subscription_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $subscription_model = new Subscription();
        $subscription = $subscription_model->get_by_id($subscription_id);

        if(!$subscription || (int) $subscription->user_id !== (int) $this->user->user_id) {
            redirect('account-billing');
        }

        if($subscription_model->pause($subscription->subscription_id)) {
            Alerts::add_success(l('account_billing.success_message.subscription_paused'));
        }

        redirect('account-billing');
    }

    public function resume_subscription() {

        \Altum\Authentication::guard();

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('account-billing');
        }

        $subscription_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $subscription_model = new Subscription();
        $subscription = $subscription_model->get_by_id($subscription_id);

        if(!$subscription || (int) $subscription->user_id !== (int) $this->user->user_id) {
            redirect('account-billing');
        }

        if($subscription_model->resume($subscription->subscription_id)) {
            Alerts::add_success(l('account_billing.success_message.subscription_resumed'));
        }

        redirect('account-billing');
    }

}
