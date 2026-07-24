<?php
/*
 * Copyright (c) 2025 Leoivard (https://flowcode.co.ke/)
 * Static landing page controller for restaurant solutions
 */

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class Restaurant extends Controller {

    public function index() {

       
        // ✅ 3. Render static view (no DB models, no dynamic content)
        $view = new \Altum\View('restaurant/index', (array) $this);
        $this->add_view_content('content', $view->run());
    }
}
