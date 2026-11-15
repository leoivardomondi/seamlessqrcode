<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Captcha;

defined('ALTUMCODE') || die();

class Contact extends Controller {

    public function index() {

        if(!settings()->email_notifications->contact || empty(settings()->email_notifications->emails)) {
            redirect('not-found');
        }

        /* Initiate captcha */
        $captcha = new Captcha();

        if(!empty($_POST)) {
            $_POST['name'] = input_clean($_POST['name'], 64);
            $_POST['email'] = input_clean_email($_POST['email'] ?? '');
            $_POST['subject'] = input_clean($_POST['subject'], 128);
            $_POST['message'] = input_clean($_POST['message'], 2048);

            //ALTUMCODE:DEMO if(DEMO) if($this->user->user_id == 1) Alerts::add_error('Please create an account on the demo to test out this function.');

            /* Check for any errors */
            $required_fields = ['name', 'email', 'subject', 'message'];
            foreach($required_fields as $field) {
                if(!isset($_POST[$field]) || (isset($_POST[$field]) && empty($_POST[$field]) && $_POST[$field] != '0')) {
                    Alerts::add_field_error($field, l('global.error_message.empty_field'));
                }
            }

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(settings()->captcha->contact_is_enabled && !$captcha->is_valid()) {
                Alerts::add_field_error('captcha', l('global.error_message.invalid_captcha'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

                /* Prepare the email */
                $email_template = get_email_template(
                    [
                        '{{NAME}}' => str_replace('.', '. ', $_POST['name']),
                        '{{SUBJECT}}' => $_POST['subject'],
                    ],
                    l('global.emails.admin_contact.subject'),
                    [
                        '{{NAME}}' => str_replace('.', '. ', $_POST['name']),
                        '{{EMAIL}}' => $_POST['email'],
                        '{{MESSAGE}}' => $_POST['message'],
                    ],
                    l('global.emails.admin_contact.body')
                );

                send_mail(explode(',', settings()->email_notifications->emails), $email_template->subject, $email_template->body, [], $_POST['email']);

                /* Send webhook notification if needed */
                if(settings()->webhooks->contact) {
                    fire_and_forget('post', settings()->webhooks->contact, [
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'subject' => $_POST['subject'],
                        'message' => $_POST['message'],
                        'datetime' => get_date(),
                    ]);
                }

                /* Set a nice success message */
                Alerts::add_success(l('contact.success_message'));

                redirect('contact');
            }
        }

        $values = [
            'name' => is_logged_in() ? $this->user->name : ($_POST['name'] ??  ''),
            'email' => is_logged_in() ? $this->user->email : ($_POST['email'] ??  ''),
            'subject' => $_POST['subject'] ?? $_GET['subject'] ?? '',
            'message' => $_POST['message'] ?? $_GET['message'] ?? '',
        ];

        /* Prepare the view */
        $data = [
            'captcha' => $captcha,
            'values' => $values,
        ];

        $view = new \Altum\View('contact/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}


