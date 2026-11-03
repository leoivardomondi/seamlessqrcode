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

class SplashPageCreate extends Controller {

    public function index() {

        if(!settings()->links->splash_page_is_enabled) {
            redirect('not-found');
        }

        \Altum\Authentication::guard();

        /* Team checks */
        if(\Altum\Teams::is_delegated() && !\Altum\Teams::has_access('create.splash_pages')) {
            Alerts::add_info(l('global.info_message.team_no_access'));
            redirect('splash-pages');
        }

        /* Check for the plan limit */
        $total_rows = database()->query("SELECT COUNT(*) AS `total` FROM `splash_pages` WHERE `user_id` = {$this->user->user_id}")->fetch_object()->total ?? 0;

        if($this->user->plan_settings->splash_pages_limit != -1 && $total_rows >= $this->user->plan_settings->splash_pages_limit) {
            Alerts::add_info(l('global.info_message.plan_feature_limit'));
            redirect('splash-pages');
        }

        $notification_handlers = (new \Altum\Models\NotificationHandlers())->get_notification_handlers_by_user_id($this->user->user_id);

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['title'] = input_clean($_POST['title'], 256);
            $_POST['description'] = input_clean($_POST['description'], 2048);
            $_POST['secondary_button_name'] = input_clean($_POST['secondary_button_name'], 256);
            $_POST['secondary_button_url'] = input_clean($_POST['secondary_button_url'], 1024);
            $_POST['custom_css'] = mb_substr(trim($_POST['custom_css']), 0, 10000);
            $_POST['custom_js'] = mb_substr(trim($_POST['custom_js']), 0, 10000);
            $_POST['ads_header'] = mb_substr(trim($_POST['ads_header']), 0, 10000);
            $_POST['ads_footer'] = mb_substr(trim($_POST['ads_footer']), 0, 10000);
            $_POST['link_unlock_seconds'] = (int) $_POST['link_unlock_seconds'];
            $_POST['auto_redirect'] = (int) isset($_POST['auto_redirect']);

            /* Appearance & Colors */
            $_POST['background_type'] = in_array($_POST['background_type'] ?? '', ['default', 'color', 'gradient', 'image']) ? $_POST['background_type'] : 'default';
            $_POST['background_color'] = input_clean($_POST['background_color'] ?? '#0f0f10', 32);
            $_POST['background_color_one'] = input_clean($_POST['background_color_one'] ?? '#18181b', 32);
            $_POST['background_color_two'] = input_clean($_POST['background_color_two'] ?? '#09090b', 32);
            $_POST['background_gradient_direction'] = input_clean($_POST['background_gradient_direction'] ?? '135deg', 32);
            $_POST['title_color'] = input_clean($_POST['title_color'] ?? '#f4f4f5', 32);
            $_POST['description_color'] = input_clean($_POST['description_color'] ?? '#a1a1aa', 32);

            /* Buttons styling */
            $_POST['primary_button_name'] = input_clean($_POST['primary_button_name'] ?? '', 256);
            $_POST['primary_button_background_color'] = input_clean($_POST['primary_button_background_color'] ?? '#eab308', 32);
            $_POST['primary_button_text_color'] = input_clean($_POST['primary_button_text_color'] ?? '#000000', 32);
            $_POST['primary_button_border_radius'] = in_array($_POST['primary_button_border_radius'] ?? '', ['rounded', 'rounded-pill', 'rounded-0', 'rounded-sm', 'rounded-lg']) ? $_POST['primary_button_border_radius'] : 'rounded';

            $_POST['secondary_button_background_color'] = input_clean($_POST['secondary_button_background_color'] ?? '#27272a', 32);
            $_POST['secondary_button_text_color'] = input_clean($_POST['secondary_button_text_color'] ?? '#ffffff', 32);
            $_POST['secondary_button_border_radius'] = in_array($_POST['secondary_button_border_radius'] ?? '', ['rounded', 'rounded-pill', 'rounded-0', 'rounded-sm', 'rounded-lg']) ? $_POST['secondary_button_border_radius'] : 'rounded';

            /* Details Capture Form */
            $_POST['details_form_is_enabled'] = (bool) isset($_POST['details_form_is_enabled']);
            $_POST['form_title'] = input_clean($_POST['form_title'] ?? '', 512);
            $_POST['form_subtitle'] = input_clean($_POST['form_subtitle'] ?? '', 512);
            $_POST['form_card_background_color'] = input_clean($_POST['form_card_background_color'] ?? '#18181b', 32);
            $_POST['form_card_text_color'] = input_clean($_POST['form_card_text_color'] ?? '#f4f4f5', 32);
            $_POST['form_card_border_color'] = input_clean($_POST['form_card_border_color'] ?? '#27272a', 32);

            $_POST['form_show_name'] = (bool) isset($_POST['form_show_name']);
            $_POST['form_name_label'] = input_clean($_POST['form_name_label'] ?? '', 128);
            $_POST['form_name_placeholder'] = input_clean($_POST['form_name_placeholder'] ?? '', 128);
            $_POST['form_name_required'] = (bool) isset($_POST['form_name_required']);

            $_POST['form_show_phone'] = (bool) isset($_POST['form_show_phone']);
            $_POST['form_phone_label'] = input_clean($_POST['form_phone_label'] ?? '', 128);
            $_POST['form_phone_placeholder'] = input_clean($_POST['form_phone_placeholder'] ?? '', 128);
            $_POST['form_phone_required'] = (bool) isset($_POST['form_phone_required']);

            $_POST['form_show_email'] = (bool) isset($_POST['form_show_email']);
            $_POST['form_email_label'] = input_clean($_POST['form_email_label'] ?? '', 128);
            $_POST['form_email_placeholder'] = input_clean($_POST['form_email_placeholder'] ?? '', 128);
            $_POST['form_email_required'] = (bool) isset($_POST['form_email_required']);

            $_POST['form_show_consent'] = (bool) isset($_POST['form_show_consent']);
            $_POST['form_consent_text'] = input_clean($_POST['form_consent_text'] ?? '', 512);
            $_POST['form_consent_required'] = (bool) isset($_POST['form_consent_required']);

            $_POST['form_submit_button_text'] = input_clean($_POST['form_submit_button_text'] ?? '', 128);
            $_POST['form_submit_button_background_color'] = input_clean($_POST['form_submit_button_background_color'] ?? '#eab308', 32);
            $_POST['form_submit_button_text_color'] = input_clean($_POST['form_submit_button_text_color'] ?? '#000000', 32);
            $_POST['form_skip_button_text'] = input_clean($_POST['form_skip_button_text'] ?? '', 128);
            $_POST['form_success_message'] = input_clean($_POST['form_success_message'] ?? '', 256);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Image uploads */
            $logo = \Altum\Uploads::process_upload(null, 'splash_pages', 'logo', 'logo_remove', settings()->links->avatar_size_limit);
            $favicon = \Altum\Uploads::process_upload(null, 'splash_pages', 'favicon', 'favicon_remove', settings()->links->favicon_size_limit);
            $opengraph = \Altum\Uploads::process_upload(null, 'splash_pages', 'opengraph', 'opengraph_remove', settings()->links->seo_image_size_limit);
            $background = \Altum\Uploads::process_upload(null, 'splash_pages', 'background', 'background_remove', settings()->links->background_size_limit ?? 5000000);

            /* Check for any errors */
            $required_fields = ['name'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                $settings = json_encode([
                    'logo' => $logo,
                    'favicon' => $favicon,
                    'opengraph' => $opengraph,
                    'background' => $background,
                    'secondary_button_name' => $_POST['secondary_button_name'],
                    'secondary_button_url' => $_POST['secondary_button_url'],
                    'custom_css' => $_POST['custom_css'],
                    'custom_js' => $_POST['custom_js'],
                    'ads_header' => $_POST['ads_header'],
                    'ads_footer' => $_POST['ads_footer'],

                    /* Appearance & Colors */
                    'background_type' => $_POST['background_type'],
                    'background_color' => $_POST['background_color'],
                    'background_color_one' => $_POST['background_color_one'],
                    'background_color_two' => $_POST['background_color_two'],
                    'background_gradient_direction' => $_POST['background_gradient_direction'],
                    'title_color' => $_POST['title_color'],
                    'description_color' => $_POST['description_color'],

                    /* Buttons */
                    'primary_button_name' => $_POST['primary_button_name'],
                    'primary_button_background_color' => $_POST['primary_button_background_color'],
                    'primary_button_text_color' => $_POST['primary_button_text_color'],
                    'primary_button_border_radius' => $_POST['primary_button_border_radius'],
                    'secondary_button_background_color' => $_POST['secondary_button_background_color'],
                    'secondary_button_text_color' => $_POST['secondary_button_text_color'],
                    'secondary_button_border_radius' => $_POST['secondary_button_border_radius'],

                    /* Details Capture Form */
                    'details_form_is_enabled' => $_POST['details_form_is_enabled'],
                    'form_title' => $_POST['form_title'],
                    'form_subtitle' => $_POST['form_subtitle'],
                    'form_card_background_color' => $_POST['form_card_background_color'],
                    'form_card_text_color' => $_POST['form_card_text_color'],
                    'form_card_border_color' => $_POST['form_card_border_color'],
                    'form_show_name' => $_POST['form_show_name'],
                    'form_name_label' => $_POST['form_name_label'],
                    'form_name_placeholder' => $_POST['form_name_placeholder'],
                    'form_name_required' => $_POST['form_name_required'],
                    'form_show_phone' => $_POST['form_show_phone'],
                    'form_phone_label' => $_POST['form_phone_label'],
                    'form_phone_placeholder' => $_POST['form_phone_placeholder'],
                    'form_phone_required' => $_POST['form_phone_required'],
                    'form_show_email' => $_POST['form_show_email'],
                    'form_email_label' => $_POST['form_email_label'],
                    'form_email_placeholder' => $_POST['form_email_placeholder'],
                    'form_email_required' => $_POST['form_email_required'],
                    'form_show_consent' => $_POST['form_show_consent'],
                    'form_consent_text' => $_POST['form_consent_text'],
                    'form_consent_required' => $_POST['form_consent_required'],
                    'form_submit_button_text' => $_POST['form_submit_button_text'],
                    'form_submit_button_background_color' => $_POST['form_submit_button_background_color'],
                    'form_submit_button_text_color' => $_POST['form_submit_button_text_color'],
                    'form_skip_button_text' => $_POST['form_skip_button_text'],
                    'form_success_message' => $_POST['form_success_message'],
                    'notifications' => array_map('intval', $_POST['notifications'] ?? []),
                ]);

                /* Database query */
                db()->insert('splash_pages', [
                    'user_id' => $this->user->user_id,
                    'name' => $_POST['name'],
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'link_unlock_seconds' => $_POST['link_unlock_seconds'],
                    'auto_redirect' => $_POST['auto_redirect'],
                    'settings' => $settings,
                    'datetime' => get_date(),
                ]);

                /* Set a nice success message */
                Alerts::add_success(sprintf(l('global.success_message.create1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Clear the cache */
                cache()->deleteItem('splash_pages?user_id=' . $this->user->user_id);

                redirect('splash-pages');
            }
        }

        $values = [
            'name' => $_POST['name'] ?? '',
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'secondary_button_name' => $_POST['secondary_button_name'] ?? '',
            'secondary_button_url' => $_POST['secondary_button_url'] ?? '',
            'link_unlock_seconds' => $_POST['link_unlock_seconds'] ?? 5,
            'auto_redirect' => $_POST['auto_redirect'] ?? false,
            'custom_css' => $_POST['custom_css'] ?? false,
            'custom_js' => $_POST['custom_js'] ?? false,
            'ads_header' => $_POST['ads_header'] ?? false,
            'ads_footer' => $_POST['ads_footer'] ?? false,

            /* Appearance & Colors */
            'background_type' => $_POST['background_type'] ?? 'default',
            'background_color' => $_POST['background_color'] ?? '#0f0f10',
            'background_color_one' => $_POST['background_color_one'] ?? '#18181b',
            'background_color_two' => $_POST['background_color_two'] ?? '#09090b',
            'background_gradient_direction' => $_POST['background_gradient_direction'] ?? '135deg',
            'title_color' => $_POST['title_color'] ?? '#f4f4f5',
            'description_color' => $_POST['description_color'] ?? '#a1a1aa',

            /* Buttons styling */
            'primary_button_name' => $_POST['primary_button_name'] ?? '',
            'primary_button_background_color' => $_POST['primary_button_background_color'] ?? '#eab308',
            'primary_button_text_color' => $_POST['primary_button_text_color'] ?? '#000000',
            'primary_button_border_radius' => $_POST['primary_button_border_radius'] ?? 'rounded',
            'secondary_button_background_color' => $_POST['secondary_button_background_color'] ?? '#27272a',
            'secondary_button_text_color' => $_POST['secondary_button_text_color'] ?? '#ffffff',
            'secondary_button_border_radius' => $_POST['secondary_button_border_radius'] ?? 'rounded',

            /* Details Capture Form */
            'details_form_is_enabled' => $_POST['details_form_is_enabled'] ?? false,
            'form_title' => $_POST['form_title'] ?? l('splash_pages.form_title_default'),
            'form_subtitle' => $_POST['form_subtitle'] ?? '',
            'form_card_background_color' => $_POST['form_card_background_color'] ?? '#18181b',
            'form_card_text_color' => $_POST['form_card_text_color'] ?? '#f4f4f5',
            'form_card_border_color' => $_POST['form_card_border_color'] ?? '#27272a',
            'form_show_name' => $_POST['form_show_name'] ?? true,
            'form_name_label' => $_POST['form_name_label'] ?? l('splash_pages.form_name_label_default'),
            'form_name_placeholder' => $_POST['form_name_placeholder'] ?? l('splash_pages.form_name_placeholder_default'),
            'form_name_required' => $_POST['form_name_required'] ?? false,
            'form_show_phone' => $_POST['form_show_phone'] ?? true,
            'form_phone_label' => $_POST['form_phone_label'] ?? l('splash_pages.form_phone_label_default'),
            'form_phone_placeholder' => $_POST['form_phone_placeholder'] ?? l('splash_pages.form_phone_placeholder_default'),
            'form_phone_required' => $_POST['form_phone_required'] ?? false,
            'form_show_email' => $_POST['form_show_email'] ?? true,
            'form_email_label' => $_POST['form_email_label'] ?? l('splash_pages.form_email_label_default'),
            'form_email_placeholder' => $_POST['form_email_placeholder'] ?? l('splash_pages.form_email_placeholder_default'),
            'form_email_required' => $_POST['form_email_required'] ?? false,
            'form_show_consent' => $_POST['form_show_consent'] ?? true,
            'form_consent_text' => $_POST['form_consent_text'] ?? l('splash_pages.form_consent_text_default'),
            'form_consent_required' => $_POST['form_consent_required'] ?? false,
            'form_submit_button_text' => $_POST['form_submit_button_text'] ?? l('splash_pages.form_submit_button_text_default'),
            'form_submit_button_background_color' => $_POST['form_submit_button_background_color'] ?? '#eab308',
            'form_submit_button_text_color' => $_POST['form_submit_button_text_color'] ?? '#000000',
            'form_skip_button_text' => $_POST['form_skip_button_text'] ?? l('splash_pages.form_skip_button_text_default'),
            'form_success_message' => $_POST['form_success_message'] ?? l('splash_pages.form_success_message_default'),
            'notifications' => $_POST['notifications'] ?? [],
        ];

        /* Prepare the view */
        $data = [
            'values' => $values,
            'notification_handlers' => $notification_handlers
        ];

        $view = new \Altum\View('splash-page-create/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
