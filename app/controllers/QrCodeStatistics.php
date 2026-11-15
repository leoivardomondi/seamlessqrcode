<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Alerts;

defined('ALTUMCODE') || die();

class QrCodeStatistics extends Controller {

    public function index() {

        \Altum\Authentication::guard();

        if(!settings()->codes->qr_codes_is_enabled) {
            redirect('not-found');
        }

        $qr_code_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$qr_code = db()->where('qr_code_id', $qr_code_id)->where('user_id', $this->user->user_id)->getOne('qr_codes', ['qr_code_id', 'link_id'])) {
            redirect('qr-codes');
        }

        if(!$qr_code->link_id || !db()->where('link_id', $qr_code->link_id)->where('user_id', $this->user->user_id)->getValue('links', 'link_id')) {
            Alerts::add_info(l('qr_codes.statistics.no_dynamic_link'));
            redirect('qr-code-update/' . $qr_code->qr_code_id);
        }

        $query_parameters = $_GET;
        unset($query_parameters['altum']);
        $query_string = http_build_query($query_parameters);

        redirect('link/' . $qr_code->link_id . '/statistics' . ($query_string ? '?' . $query_string : ''));
    }

}
