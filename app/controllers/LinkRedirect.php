<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Models\Domain;

defined('ALTUMCODE') || die();

class LinkRedirect extends Controller {

    public function index() {

        $link_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$link = db()->where('link_id', $link_id)->getOne('links', ['link_id', 'domain_id', 'user_id', 'url'])) {
            redirect();
        }

        /* Get the current domain if needed */
        $link->domain = $link->domain_id ? (new Domain())->get_domain_by_domain_id($link->domain_id) : null;

        /* Determine the actual full url */
        $link->full_url = $link->domain_id && isset($link->domain) ? $link->domain->url . '/' . ($link->domain->link_id == $link->link_id ? null : $link->url) : SITE_URL . $link->url;

        header('Location: ' . $link->full_url);

        die();

    }
}
