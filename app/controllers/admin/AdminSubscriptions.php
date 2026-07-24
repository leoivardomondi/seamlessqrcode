<?php

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\Plan;
use Altum\Models\Subscription;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class AdminSubscriptions extends Controller {

    public function index() {

        $subscription_model = new Subscription();
        $subscription_model->ensure_schema();
        $subscription_model->refresh_empty_amounts_from_plan_prices();
        $subscription_model->refresh_default_currency_amounts();

        $payment_processors = require APP_PATH . 'includes/payment_processors.php';

        $filters = (new \Altum\Filters(['status', 'plan_id', 'user_id', 'processor', 'frequency'], ['external_subscription_id'], ['subscription_id', 'total_amount', 'current_period_end', 'datetime']));
        $filters->set_default_order_by('subscription_id', $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `subscriptions` WHERE 1 = 1 {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('admin/subscriptions?' . $filters->get_get() . '&page=%d')));

        $subscriptions = [];
        $subscriptions_result = database()->query("
            SELECT
                `subscriptions`.*,
                `users`.`name` AS `user_name`,
                `users`.`email` AS `user_email`,
                `users`.`avatar` AS `user_avatar`,
                `plans`.`name` AS `plan_name`
            FROM
                `subscriptions`
            LEFT JOIN
                `users` ON `subscriptions`.`user_id` = `users`.`user_id`
            LEFT JOIN
                `plans` ON `subscriptions`.`plan_id` = `plans`.`plan_id`
            WHERE
                1 = 1
                {$filters->get_sql_where('subscriptions')}
                {$filters->get_sql_order_by('subscriptions')}
            {$paginator->get_sql_limit()}
        ");

        while($row = $subscriptions_result->fetch_object()) {
            $subscriptions[] = $row;
        }

        process_export_json($subscriptions, 'include', ['subscription_id', 'user_id', 'plan_id', 'status', 'type', 'frequency', 'currency', 'total_amount', 'total_amount_default_currency', 'processor', 'external_subscription_id', 'auto_collection', 'current_period_start', 'current_period_end', 'cancel_at', 'canceled_at', 'pause_start', 'pause_end', 'datetime', 'last_datetime']);
        process_export_csv($subscriptions, 'include', ['subscription_id', 'user_id', 'plan_id', 'status', 'type', 'frequency', 'currency', 'total_amount', 'total_amount_default_currency', 'processor', 'external_subscription_id', 'auto_collection', 'current_period_start', 'current_period_end', 'cancel_at', 'canceled_at', 'pause_start', 'pause_end', 'datetime', 'last_datetime']);

        $plans = (new Plan())->get_plans();

        $stats = [
            'active' => 0,
            'trialing' => 0,
            'past_due' => 0,
            'paused' => 0,
            'non_renewing' => 0,
            'canceled' => 0,
        ];
        $stats_result = database()->query("SELECT `status`, COUNT(*) AS `total` FROM `subscriptions` GROUP BY `status`");
        while($row = $stats_result->fetch_object()) {
            $stats[$row->status] = (int) $row->total;
        }

        $mrr = 0;
        $default_currency = settings()->payment->default_currency;
        $mrr_result = database()->query("SELECT `frequency`, `total_amount`, `total_amount_default_currency`, `currency` FROM `subscriptions` WHERE `status` IN ('trialing', 'active', 'past_due')");
        while($row = $mrr_result->fetch_object()) {
            $total_amount = $row->total_amount_default_currency !== null
                ? (float) $row->total_amount_default_currency
                : ($row->currency == $default_currency ? (float) $row->total_amount : 0);

            $mrr += match($row->frequency) {
                'monthly' => $total_amount,
                'quarterly' => $total_amount / 3,
                'biannual' => $total_amount / 6,
                'annual' => $total_amount / 12,
                default => 0,
            };
        }
        $arr = $mrr * 12;

        $pagination = (new \Altum\View('partials/admin_pagination', (array) $this))->run(['paginator' => $paginator]);

        $data = [
            'subscriptions' => $subscriptions,
            'plans' => $plans,
            'stats' => $stats,
            'mrr' => $mrr,
            'arr' => $arr,
            'pagination' => $pagination,
            'filters' => $filters,
            'payment_processors' => $payment_processors,
        ];

        $view = new \Altum\View('admin/subscriptions/index', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }

    public function view() {

        $subscription_model = new Subscription();
        $subscription_model->ensure_schema();
        $subscription_model->refresh_invoice_dates_from_payments();

        $subscription_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $subscription = $subscription_model->get_by_id($subscription_id);

        if(!$subscription) {
            redirect('admin/subscriptions');
        }

        $user = db()->where('user_id', $subscription->user_id)->getOne('users');
        $plan = (new Plan())->get_plan_by_id($subscription->plan_id);
        $items = db()->where('subscription_id', $subscription->subscription_id)->get('subscription_items');
        $invoices = db()->where('subscription_id', $subscription->subscription_id)->orderBy('invoice_id', 'DESC')->get('invoices');
        $events = db()->where('subscription_id', $subscription->subscription_id)->orderBy('billing_event_id', 'DESC')->get('billing_events', 50);
        $dunning_attempts = db()->where('subscription_id', $subscription->subscription_id)->orderBy('dunning_attempt_id', 'DESC')->get('dunning_attempts', 50);
        $entitlements = $subscription_model->get_entitlements($subscription->subscription_id);

        $data = [
            'subscription' => $subscription,
            'user' => $user,
            'plan' => $plan,
            'items' => $items,
            'invoices' => $invoices,
            'events' => $events,
            'dunning_attempts' => $dunning_attempts,
            'entitlements' => $entitlements,
        ];

        $view = new \Altum\View('admin/subscriptions/view', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }

    public function cancel() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription->external_subscription_id) {
            try {
                (new User())->cancel_subscription($subscription->user_id);
            } catch(\Exception $exception) {
                Alerts::add_error($exception->getCode() . ':' . $exception->getMessage());
                redirect('admin/subscriptions/view/' . $subscription->subscription_id);
            }
        } else {
            $subscription_model->cancel($subscription->subscription_id, 'admin_request', true);
        }

        Alerts::add_success(l('admin_subscriptions.success_message.subscription_canceled'));
        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function pause() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription_model->pause($subscription->subscription_id)) {
            Alerts::add_success(l('admin_subscriptions.success_message.subscription_paused'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function resume() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription_model->resume($subscription->subscription_id)) {
            Alerts::add_success(l('admin_subscriptions.success_message.subscription_resumed'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function mark_past_due() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription_model->mark_past_due($subscription->subscription_id, null, 'admin_request')) {
            Alerts::add_success(l('admin_subscriptions.success_message.subscription_past_due'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function apply_manual_period() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        $_POST['frequency'] = query_clean($_POST['frequency'] ?? '');
        $_POST['currency'] = query_clean($_POST['currency'] ?? '');
        $_POST['payment_datetime'] = query_clean($_POST['payment_datetime'] ?? '');

        $result = $subscription_model->apply_manual_period(
            $subscription->subscription_id,
            $_POST['frequency'],
            $_POST['currency'] ?: null,
            $_POST['payment_datetime'] ?: null
        );

        if($result) {
            Alerts::add_success(sprintf(
                l('admin_subscriptions.success_message.manual_period_applied'),
                nr($result['amount'], 2) . ' ' . $result['currency'],
                l('plan.custom_plan.' . $result['frequency']),
                \Altum\Date::get($result['period_end'], 2)
            ));
        } else {
            Alerts::add_error(l('admin_subscriptions.error_message.manual_period_failed'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function update_invoice_paid_datetime() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $invoice_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $invoice = $subscription_model->get_invoice_by_id($invoice_id);

        if(!$invoice || !$invoice->subscription_id) {
            Alerts::add_error(l('admin_subscriptions.error_message.invoice_paid_datetime_failed'));
            redirect('admin/subscriptions');
        }

        $_POST['paid_datetime'] = query_clean($_POST['paid_datetime'] ?? '');

        if($subscription_model->update_invoice_paid_datetime($invoice->invoice_id, $_POST['paid_datetime'])) {
            Alerts::add_success(l('admin_subscriptions.success_message.invoice_paid_datetime_updated'));
        } else {
            Alerts::add_error(l('admin_subscriptions.error_message.invoice_paid_datetime_failed'));
        }

        redirect('admin/subscriptions/view/' . $invoice->subscription_id);
    }

    public function sync_user() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription_model->sync_user_from_subscription($subscription->subscription_id)) {
            Alerts::add_success(l('admin_subscriptions.success_message.user_synced'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    public function sync_from_user() {
        $this->guard_action();

        $subscription_model = new Subscription();
        $subscription = $this->get_subscription_or_redirect($subscription_model);

        if($subscription_model->sync_from_user_plan($subscription->user_id, 'admin_subscription_action')) {
            Alerts::add_success(l('admin_subscriptions.success_message.subscription_synced_from_user'));
        }

        redirect('admin/subscriptions/view/' . $subscription->subscription_id);
    }

    private function guard_action() {
        if(!\Altum\Csrf::check('global_token')) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('admin/subscriptions');
        }
    }

    private function get_subscription_or_redirect(Subscription $subscription_model) {
        $subscription_id = isset($this->params[0]) ? (int) $this->params[0] : null;
        $subscription = $subscription_model->get_by_id($subscription_id);

        if(!$subscription) {
            redirect('admin/subscriptions');
        }

        return $subscription;
    }

}
