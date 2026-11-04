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

defined('ALTUMCODE') || die();

class SplashPages extends Controller {

    public function index() {

        if(!settings()->links->splash_page_is_enabled) {
            redirect('not-found');
        }

        \Altum\Authentication::guard();

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters([], ['name'], ['splash_page_id', 'last_datetime', 'name', 'datetime']));
        $filters->set_default_order_by($this->user->preferences->splash_pages_default_order_by, $this->user->preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($this->user->preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Prepare the paginator */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `splash_pages` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()}")->fetch_object()->total ?? 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('splash-pages?' . $filters->get_get() . '&page=%d')));

        /* Get the splash_pages list for the user */
        $splash_pages = [];
        $splash_pages_result = database()->query("SELECT * FROM `splash_pages` WHERE `user_id` = {$this->user->user_id} {$filters->get_sql_where()} {$filters->get_sql_order_by()} {$paginator->get_sql_limit()}");
        while($row = $splash_pages_result->fetch_object()) {
            $row->settings = json_decode($row->settings ?? '');
            $splash_pages[] = $row;
        }

        /* Export handler */
        process_export_csv($splash_pages, 'include', ['splash_page_id', 'user_id', 'name', 'color', 'last_datetime', 'datetime'], sprintf(l('splash_pages.title')));
        process_export_json($splash_pages, 'include', ['splash_page_id', 'user_id', 'name', 'color', 'last_datetime', 'datetime'], sprintf(l('splash_pages.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'splash_pages' => $splash_pages,
            'total_splash_pages' => $total_rows,
            'pagination' => $pagination,
            'filters' => $filters,
        ];

        $view = new \Altum\View('splash-pages/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    public function bulk() {

        if(!settings()->links->splash_page_is_enabled) {
            redirect('not-found');
        }

        \Altum\Authentication::guard();

        /* Check for any errors */
        if(empty($_POST)) {
            redirect('splash-pages');
        }

        if(empty($_POST['selected'])) {
            redirect('splash-pages');
        }

        if(!isset($_POST['type'])) {
            redirect('splash-pages');
        }

        //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            set_time_limit(0);

            switch($_POST['type']) {
                case 'delete':

                    /* Team checks */
                    if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.splash_pages')) {
                        Alerts::add_info(l('global.info_message.team_no_access'));
                        redirect('splash-pages');
                    }

                    foreach($_POST['selected'] as $splash_page_id) {
                        if($splash_page = db()->where('splash_page_id', $splash_page_id)->where('user_id', $this->user->user_id)->getOne('splash_pages', ['splash_page_id'])) {
                            (new \Altum\Models\SplashPages())->delete($splash_page->splash_page_id);
                        }
                    }

                    break;
            }

            /* Set a nice success message */
            Alerts::add_success(l('bulk_delete_modal.success_message'));

        }

        redirect('splash-pages');
    }

    public function delete() {

        if(!settings()->links->splash_page_is_enabled) {
            redirect('not-found');
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('delete.splash_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('splash-pages');
        }

        if(empty($_POST)) {
            redirect('splash-pages');
        }

        $splash_page_id = (int) $_POST['splash_page_id'];

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$splash_page = db()->where('splash_page_id', $splash_page_id)->where('user_id', $this->user->user_id)->getOne('splash_pages', ['splash_page_id', 'name'])) {
            redirect('splash-pages');
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            (new \Altum\Models\SplashPages())->delete($splash_page->splash_page_id);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('global.success_message.delete1'), '<strong>' . $splash_page->name . '</strong>'));

            redirect('splash-pages');
        }

        redirect('splash-pages');
    }


    public function transfer() {

        if(!settings()->links->splash_page_is_enabled) {
            redirect('not-found');
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('update.splash_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('splash-pages');
        }

        if(empty($_POST)) {
            redirect('splash-pages');
        }

        $splash_page_id = (int) $_POST['splash_page_id'];
        $_POST['email'] = input_clean_email($_POST['email'] ?? '');

        //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        if(!$splash_page = db()->where('splash_page_id', $splash_page_id)->where('user_id', $this->user->user_id)->getOne('splash_pages', ['splash_page_id', 'user_id', 'name'])) {
            redirect('splash-pages');
        }

        if(!$current_user = db()->where('user_id', $splash_page->user_id)->getOne('users', ['user_id', 'email'])) {
            redirect('splash-pages');
        }

        if(!$new_user = db()->where('email', $_POST['email'])->getOne('users', ['user_id', 'email', 'status', 'plan_settings'])) {
            Alerts::add_error(l('transfer_modal.error_message.email_not_found'));
        }

        if(!empty($new_user) && $current_user->user_id == $new_user->user_id) {
            Alerts::add_error(l('transfer_modal.error_message.self_transfer'));
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

            /* Update the database */
            db()->where('splash_page_id', $splash_page->splash_page_id)->update('splash_pages', [
                'user_id' => $new_user->user_id,
                'last_datetime' => get_date(),
            ]);

            /* Detach from current user links */
            db()->where('splash_page_id', $splash_page->splash_page_id)->where('user_id', $current_user->user_id)->update('links', [
                'splash_page_id' => null,
            ]);

            /* Set a nice success message */
            Alerts::add_success(sprintf(l('transfer_modal.success_message'), '<strong>' . input_clean($splash_page->name) . '</strong>', '<strong>' . input_clean($current_user->email) . '</strong>', '<strong>' . input_clean($new_user->email) . '</strong>'));

            /* Clear the cache */
            cache()->deleteItem('splash_pages?user_id=' . $current_user->user_id);
            cache()->deleteItem('splash_pages?user_id=' . $new_user->user_id);
            cache()->deleteItemsByTag('user_id=' . $current_user->user_id);
            cache()->deleteItemsByTag('user_id=' . $new_user->user_id);

            redirect('splash-pages');
        }

        if(!empty($_POST['original_request'])) {
            redirect(base64_decode($_POST['original_request']) . (!empty($_POST['original_request_query']) ? '?' . base64_decode($_POST['original_request_query']) : null));
        }

        redirect('splash-pages');
    }
}
