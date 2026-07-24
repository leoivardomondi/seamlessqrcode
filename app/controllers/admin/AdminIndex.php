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

use Altum\Alerts;
use Altum\Response;

defined('ALTUMCODE') || die();

class AdminIndex extends Controller {

    public function index() {

        if(settings()->internal_notifications->admins_is_enabled) {
            $internal_notifications = db()->where('for_who', 'admin')->orderBy('internal_notification_id', 'DESC')->get('internal_notifications', 5);

            if(!\Altum\Authentication::is_superadmin()) {
                $internal_notifications = array_values(array_filter($internal_notifications, function($notification) {
                    $content = mb_strtolower(($notification->title ?? '') . ' ' . ($notification->description ?? '') . ' ' . ($notification->url ?? ''));

                    foreach(['payment', 'payments', 'paid', 'invoice', 'subscription', 'revenue', 'earnings'] as $keyword) {
                        if(mb_strpos($content, $keyword) !== false) {
                            return false;
                        }
                    }

                    return true;
                }));
            }

            $should_set_all_read = false;
            foreach($internal_notifications as $notification) {
                if(!$notification->is_read) $should_set_all_read = true;
            }

            if($should_set_all_read) {
                db()->where('for_who', 'admin')->update('internal_notifications', [
                    'is_read' => 1,
                    'read_datetime' => get_date(),
                ]);
            }
        }

        /* Requested plan details */
        $plans = (new \Altum\Models\Plan())->get_plans();

        /* Main View */
        $data = [
            'plans' => $plans,
            'internal_notifications' => $internal_notifications ?? [],
        ];

        $view = new \Altum\View('admin/index/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function get_stats_ajax() {
        if(!empty($_POST)) {
            redirect();
        }

        set_time_limit(0);

        /* Get stats */
        $biolink_links = db()->where('type', 'biolink')->getValue('links', 'count(`link_id`)');
        $shortened_links = db()->where('type', 'link')->getValue('links', 'count(`link_id`)');
        $track_links = (int) database()->query("
            SELECT
                (
                    (SELECT COALESCE(SUM(`clicks`), 0) FROM `links`)
                    +
                    (SELECT COALESCE(SUM(`clicks`), 0) FROM `biolinks_blocks`)
                ) AS `total`
        ")->fetch_object()->total;
        $qr_codes = db()->getValue('qr_codes', 'count(`qr_code_id`)');
        $domains = db()->getValue('domains', 'count(`domain_id`)');
        $users = db()->getValue('users', 'count(`user_id`)');

        if(\Altum\Authentication::is_superadmin() && in_array(settings()->license->type, ['Extended License', 'extended'])) {
            $payments = db()->getValue('payments', 'count(`id`)');
            $payments_total_amount = db()->getValue('payments', 'sum(`total_amount_default_currency`)');
        } else {
            $payments = $payments_total_amount = 0;
        }

        $redeemed_codes = \Altum\Authentication::is_superadmin() ? db()->getValue('redeemed_codes', 'count(`id`)') : 0;

        /* Widgets stats: current month */
        $domains_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('domains', 'count(*)');
        $biolink_links_current_month = db()->where('type', 'biolink')->where('datetime', date('Y-m-01'), '>=')->getValue('links', 'count(*)');
        $shortened_links_current_month = db()->where('type', 'link')->where('datetime', date('Y-m-01'), '>=')->getValue('links', 'count(*)');
        $track_links_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('track_links', 'count(*)');
        $qr_codes_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('qr_codes', 'count(*)');
        $users_current_month = db()->where('datetime', date('Y-m-01'), '>=')->getValue('users', 'count(*)');
        $payments_current_month = \Altum\Authentication::is_superadmin() && in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('datetime', date('Y-m-01'), '>=')->getValue('payments', 'count(*)') : 0;
        $payments_amount_current_month = \Altum\Authentication::is_superadmin() && in_array(settings()->license->type, ['Extended License', 'extended']) ? db()->where('datetime', date('Y-m-01'), '>=')->getValue('payments', 'sum(`total_amount_default_currency`)') : 0;
        $redeemed_codes_current_month = \Altum\Authentication::is_superadmin() ? db()->where('datetime', date('Y-m-01'), '>=')->getValue('redeemed_codes', 'count(*)') : 0;

        /* Get currently active users */
        $fifteen_minutes_ago_datetime = (new \DateTime())->modify('-15 minutes')->format('Y-m-d H:i:s');
        $active_users = db()->where('last_activity', $fifteen_minutes_ago_datetime, '>=')->getValue('users', 'COUNT(*)');

        /* Prepare the data */
        $data = [
            'biolink_links' => $biolink_links,
            'shortened_links' => $shortened_links,
            'track_links' => $track_links,
            'qr_codes' => $qr_codes,
            'domains' => $domains,
            'payments_total_amount' => $payments_total_amount,
            'users' => $users,
            'payments' => $payments,
            'redeemed_codes' => $redeemed_codes,

            'domains_current_month' => $domains_current_month,
            'biolink_links_current_month' => $biolink_links_current_month,
            'shortened_links_current_month' => $shortened_links_current_month,
            'track_links_current_month' => $track_links_current_month,
            'qr_codes_current_month' => $qr_codes_current_month,
            'users_current_month' => $users_current_month,
            'payments_current_month' => $payments_current_month,
            'payments_amount_current_month' => $payments_amount_current_month,
            'redeemed_codes_current_month' => $redeemed_codes_current_month,

            'active_users' => $active_users,
        ];

        /* Set a nice success message */
        Response::json('', 'success', $data);

    }

    public function clear_cache() {
        if(empty($_POST)) {
            redirect('admin/index');
        }

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            redirect('admin/index');
        }

        cache()->clear();

        \Altum\Language::clear_cache();

        Alerts::add_success('Cache cleared successfully.');

        redirect('admin/index');
    }

}
