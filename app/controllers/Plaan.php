<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 */

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Plan extends Controller {

    public function index() {

        if(!settings()->payment->is_enabled) {
            redirect('not-found');
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['renew', 'upgrade', 'new']) ? $this->params[0] : 'new';

        /* If the user is not logged in when trying to upgrade or renew, make sure to redirect them */
        if(in_array($type, ['renew', 'upgrade']) && !is_logged_in()) {
            redirect('plan/new');
        }

        /* Set a custom title */
        Title::set(l('plan.header_' . $type));

        /* ✅ Load all plans */
        $all_plans = (new \Altum\Models\Plan())->get_plans();

        /* ✅ Exclude lifetime plans */
        $non_lifetime_plans = array_filter($all_plans, function($plan) {
            return $plan->status == 1 && (
                !isset($plan->prices->lifetime->{currency()}) ||
                $plan->prices->lifetime->{currency()} <= 0
            );
        });

        /* ✅ Pass filtered plans into the partial view */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run(['plans' => $non_lifetime_plans]));

        /* Prepare the view */
        $data = [
            'type' => $type
        ];

        $view = new \Altum\View('plan/index', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }
}
