<?php
/*
 * Copyright (c) 2026 leoivard
 */

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Plan extends Controller {

    public function index() {

        if(!settings()->payment->is_enabled) {
            throw_404();
        }

        if(is_logged_in()) {
            (new \Altum\Models\User())->check_and_enforce_trial_eligibility($this->user);
        }

        $type = isset($this->params[0]) && in_array($this->params[0], ['renew', 'upgrade', 'new']) ? $this->params[0] : 'new';

        /* If the user is not logged in when trying to upgrade or renew, make sure to redirect them */
        if(in_array($type, ['renew', 'upgrade']) && !is_logged_in()) {
            redirect('plan/new');
        }

        

        /* Set a custom title */
        Title::set(l('plan.header_' . $type));

        /* Plans View */
        $all_plans = (new \Altum\Models\Plan())->get_plans();

        /* Custom: hide lifetime plans from the normal pricing page. */
        $plans = array_filter($all_plans, function($plan) {
            return $plan->status == 1 && (
                !isset($plan->prices->lifetime->{currency()}) ||
                $plan->prices->lifetime->{currency()} <= 0
            );
        });

        $data = ['plans' => $plans];

        $view = new \Altum\View('partials/plans', (array) $this);

        $this->add_view_content('plans', $view->run($data));


        /* Prepare the view */
        $data = [
            'type' => $type
        ];

        $view = new \Altum\View('plan/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
