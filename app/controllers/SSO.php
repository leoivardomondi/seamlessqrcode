<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class SSO extends Controller {

    public function index() {
        redirect('not-found');
    }

    public function switch() {

        \Altum\Authentication::guard();

        $to = isset($_GET['to']) && array_key_exists($_GET['to'], (array) settings()->sso->websites) ? input_clean($_GET['to']) : null;
        $redirect = isset($_GET['redirect']) ? input_clean($_GET['redirect']) : 'dashboard';

        if(!$to) {
            redirect();
        }

        $website = settings()->sso->websites->{$to};
        $plan_name = (string) (($this->user->plan->translations->{\Altum\Language::$name}->name ?? '') ?: ($this->user->plan->name ?? $this->user->plan_id ?? ''));
        $flipbook_entitlements = $this->get_flipbook_entitlements($this->user->plan_settings ?? null);

        $response = \Unirest\Request::post(
            $website->url . 'admin-api/sso/login',
            ['Authorization' => 'Bearer ' . $website->api_key],
            \Unirest\Request\Body::form([
                'email' => $this->user->email,
                'name' => $this->user->name,
                'redirect' => $redirect,
                'plan_id' => $this->user->plan_id,
                'plan_name' => $plan_name,
                'flipbooks_limit' => $flipbook_entitlements['flipbooks_limit'],
                'edit_pdf_directly' => (int) $flipbook_entitlements['edit_pdf_directly'],
                'flipbook_entitlements' => json_encode($flipbook_entitlements),
            ])
        );

        /* Check against errors */
        if($response->code >= 400) {
            \Altum\Alerts::add_error($response->body->errors[0]->title);
            redirect('dashboard');
        }

        /* Redirect */
        header('Location: ' . $response->body->data->url); die();

    }

    private function get_flipbook_entitlements($plan_settings) {
        $flipbooks_limit = (int) ($plan_settings->flipbooks_limit ?? 0);

        $entitlements = [
            'flipbooks_limit' => $flipbooks_limit,
            'edit_pdf_directly' => $flipbooks_limit != 0,
        ];

        return $entitlements;
    }

}
