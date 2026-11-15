<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class Templates extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(!\Altum\Plugin::is_active('aix') || !settings()->aix->documents_is_enabled) {
            redirect('not-found');
        }

        /* Get available templates categories */
        $templates_categories = (new \Altum\Models\TemplatesCategories())->get_templates_categories();

        /* Templates */
        $templates = (new \Altum\Models\Templates())->get_templates();

        /* Prepare the view */
        $data = [
            'templates' => $templates,
            'templates_categories' => $templates_categories,
        ];

        $view = new \Altum\View(\Altum\Plugin::get('aix')->path . 'views/templates/index', (array) $this, true);

        $this->add_view_content('content', $view->run($data));
    }

}
