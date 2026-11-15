<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Title;

defined('ALTUMCODE') || die();

class AdminLog extends Controller {

    public function index() {

        /* Clear files caches */
        clearstatcache();

        $log_id = isset($this->params[0]) ? input_clean($this->params[0]) : null;

        if(!$log_id) {
            redirect('admin/logs');
        }

        $log_id = preg_replace('/[^a-zA-Z0-9-]/', '', $log_id);

        if(!file_exists(UPLOADS_PATH . 'logs/' . $log_id . '.log')) {
            redirect('admin/logs');
        }

        $log = (object) [
            'name' => $log_id,
            'full_name' => $log_id . '.log',
            'extension' => 'log',
            'size' => filesize(UPLOADS_PATH . 'logs/' . $log_id . '.log'),
            'last_modified' => date('Y-m-d H:i:s', filemtime(UPLOADS_PATH . 'logs/' . $log_id . '.log')),
            'content' => new \SplFileObject(UPLOADS_PATH . 'logs/' . $log_id . '.log'),
        ];

        /* Set a custom title */
        Title::set(sprintf(l('admin_log.title'), $log_id));

        /* Main View */
        $data = [
            'log_id' => $log_id,
            'log' => $log,
        ];

        $view = new \Altum\View('admin/log/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
