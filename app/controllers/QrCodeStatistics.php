<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * View all other existing AltumCode projects via https://altumcode.com/
 * Get in touch for support or general queries via https://altumcode.com/contact
 * Download the latest version via https://altumcode.com/downloads
 *
 * X/Twitter: https://x.com/AltumCode
 * Facebook: https://facebook.com/altumcode
 * Instagram: https://instagram.com/altumcode
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
