<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class AdminTaxUpdate extends Controller {

    public function index() {

        $tax_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$tax = db()->where('tax_id', $tax_id)->getOne('taxes')) {
            redirect('admin/taxes');
        }

        $tax->countries = json_decode($tax->countries);

        if(!empty($_POST)) {

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {

//                /* Database query */
//                db()->where('tax_id', $tax_id)->update('taxes', []);
//
//                /* Set a nice success message */
//                Alerts::add_success(sprintf(l('global.success_message.update1'), '<strong>' . $_POST['name'] . '</strong>'));

                /* Refresh the page */
                redirect('admin/tax-update/' . $tax_id);

            }

        }

        /* Main View */
        $data = [
            'tax_id'       => $tax_id,
            'tax'          => $tax,
        ];

        $view = new \Altum\View('admin/tax-update/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
