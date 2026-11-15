<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\controllers;

defined('ALTUMCODE') || die();

class Maintenance extends Controller {

    public function index() {

        if(!settings()->main->maintenance_is_enabled) {
            redirect();
        }

        header('HTTP/1.1 503 Service Unavailable');
        header('Retry-After: 3600');

        /* Prepare the view */
        $data = [];

        $view = new \Altum\View('maintenance/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
