<?php

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\SetupProject;

defined('ALTUMCODE') || die();

class AdminSetupProjects extends Controller {

    public function index() {
        (new SetupProject())->ensure_table();

        $projects = [];
        $result = database()->query("
            SELECT `setup_projects`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM `setup_projects`
            LEFT JOIN `users` ON `setup_projects`.`user_id` = `users`.`user_id`
            ORDER BY `setup_projects`.`setup_project_id` DESC
            LIMIT 100
        ");

        while($row = $result->fetch_object()) {
            $row->details = json_decode($row->details ?? '');
            $row->assets = json_decode($row->assets ?? '');
            $row->recommendation = json_decode($row->recommendation ?? '');
            $projects[] = $row;
        }

        $view = new \Altum\View('admin/setup-projects/index', (array) $this);
        $this->add_view_content('content', $view->run(['projects' => $projects]));
    }

    public function view() {
        (new SetupProject())->ensure_table();

        $setup_project_id = isset($this->params[0]) ? (int) $this->params[0] : null;

        if(!$project = database()->query("
            SELECT `setup_projects`.*, `users`.`name` AS `user_name`, `users`.`email` AS `user_email`
            FROM `setup_projects`
            LEFT JOIN `users` ON `setup_projects`.`user_id` = `users`.`user_id`
            WHERE `setup_projects`.`setup_project_id` = {$setup_project_id}
        ")->fetch_object()) {
            redirect('admin/setup-projects');
        }

        $project->details = json_decode($project->details ?? '');
        $project->assets = json_decode($project->assets ?? '');
        $project->recommendation = json_decode($project->recommendation ?? '');

        if(!empty($_POST)) {
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                db()->where('setup_project_id', $setup_project_id)->update('setup_projects', [
                    'status' => input_clean($_POST['status'] ?? 'new', 32),
                    'admin_notes' => input_clean($_POST['admin_notes'] ?? '', 4096),
                    'last_datetime' => get_date(),
                ]);

                Alerts::add_success(l('global.success_message.update2'));
                redirect('admin/setup-projects/view/' . $setup_project_id);
            }
        }

        $view = new \Altum\View('admin/setup-projects/view', (array) $this);
        $this->add_view_content('content', $view->run(['project' => $project]));
    }
}
