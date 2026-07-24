<?php

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\SetupProject as SetupProjectModel;

defined('ALTUMCODE') || die();

class SetupProject extends Controller {

    public function index() {
        \Altum\Authentication::guard();

        if(empty($_POST)) {
            redirect('plan');
        }

        $model = new SetupProjectModel();
        $model->ensure_table();

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
        }

        $industry = input_clean($_POST['industry'] ?? 'other', 64);
        $business_name = input_clean($_POST['business_name'] ?? '', 128);
        $return_url = $this->get_return_url($_POST['return_url'] ?? 'plan');
        $pricing_context = in_array($_POST['pricing_context'] ?? 'recurring', ['recurring', 'lifetime']) ? $_POST['pricing_context'] : 'recurring';
        $is_hospitality = in_array($industry, ['restaurant', 'hotel', 'lounge', 'catering']);
        $is_corporate = $industry == 'corporate';

        $details = [
            'industry' => $industry,
            'business_name' => $business_name,
            'branches_count' => $is_hospitality && $industry != 'hotel' ? max(1, (int) ($_POST['branches_count'] ?? 1)) : 1,
            'hotel_outlets_count' => $industry == 'hotel' ? max(1, (int) ($_POST['hotel_outlets_count'] ?? 1)) : 1,
            'menu_categories_count' => $is_hospitality ? max(1, (int) ($_POST['menu_categories_count'] ?? 1)) : 1,
            'staff_cards_count' => $is_corporate ? max(1, (int) ($_POST['staff_cards_count'] ?? 1)) : max(1, (int) ($_POST['staff_cards_count'] ?? 1)),
            'department_pages_count' => 0,
            'contact' => $is_corporate ? '' : input_clean($_POST['contact'] ?? '', 128),
            'branch_names' => $is_hospitality ? input_clean($_POST['branch_names'] ?? '', 1024) : '',
            'menu_notes' => $is_corporate ? '' : input_clean($_POST['menu_notes'] ?? '', 2048),
            'brochure_landing_page' => $is_corporate && in_array($_POST['brochure_landing_page'] ?? 'not_sure', ['yes', 'no', 'not_sure']) ? $_POST['brochure_landing_page'] : null,
            'brochure_count' => $is_corporate ? max(0, (int) ($_POST['brochure_count'] ?? 0)) : 0,
            'goals' => $_POST['goals'] ?? [],
        ];

        $assets = [
            'logo' => null,
            'menus' => [],
        ];

        if($is_hospitality && !Alerts::has_field_errors() && !Alerts::has_errors()) {
            $assets = $this->process_assets();
        }

        if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
            $recommendation = $model->recommend_plan($details, $pricing_context);

            $setup_project_id = db()->insert('setup_projects', [
                'user_id' => $this->user->user_id,
                'industry' => $industry,
                'business_name' => $business_name,
                'recommended_plan_id' => $recommendation['plan_id'],
                'recommended_plan_name' => $recommendation['plan_name'],
                'recommendation' => json_encode($recommendation),
                'details' => json_encode($details),
                'assets' => json_encode($assets),
                'datetime' => get_date(),
                'last_datetime' => get_date(),
            ]);

            Alerts::add_success('Your setup planner was saved. We recommended the best package based on your answers.');
            redirect($return_url . (str_contains($return_url, '?') ? '&' : '?') . 'setup_project_id=' . $setup_project_id . '#setup-planner');
        }

        redirect($return_url . '#setup-planner');
    }

    private function get_return_url($return_url) {
        $return_url = trim((string) $return_url);

        if(str_starts_with($return_url, SITE_URL)) {
            $return_url = mb_substr($return_url, mb_strlen(SITE_URL));
        }

        $return_url = ltrim($return_url, '/');

        if(!$return_url || str_contains($return_url, '://') || str_starts_with($return_url, 'http')) {
            return 'plan';
        }

        $path = parse_url($return_url, PHP_URL_PATH);

        if(!in_array($path, ['plan', 'lifetime'])) {
            return 'plan';
        }

        return $return_url;
    }

    private function process_assets() {
        $upload_path = UPLOADS_PATH . \Altum\Uploads::get_path('setup_projects');

        if(!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $assets = [
            'logo' => null,
            'menus' => [],
        ];

        if(!empty($_FILES['logo_file']['name'])) {
            $assets['logo'] = $this->move_uploaded_file('logo_file', ['jpg', 'jpeg', 'png', 'webp', 'svg']);
        }

        if(!empty($_FILES['menu_files']['name']) && is_array($_FILES['menu_files']['name'])) {
            foreach($_FILES['menu_files']['name'] as $index => $name) {
                if(!$name) continue;

                $file = [
                    'name' => $_FILES['menu_files']['name'][$index],
                    'type' => $_FILES['menu_files']['type'][$index],
                    'tmp_name' => $_FILES['menu_files']['tmp_name'][$index],
                    'error' => $_FILES['menu_files']['error'][$index],
                    'size' => $_FILES['menu_files']['size'][$index],
                ];

                $moved_file = $this->move_uploaded_array_file($file, ['pdf'], 50);

                if($moved_file) {
                    $assets['menus'][] = [
                        'file' => $moved_file,
                        'original_name' => input_clean($name, 128),
                    ];
                }
            }
        }

        return $assets;
    }

    private function move_uploaded_file($file_key, $allowed_extensions) {
        return $this->move_uploaded_array_file($_FILES[$file_key], $allowed_extensions);
    }

    private function move_uploaded_array_file($file, $allowed_extensions, $max_size_mb = null) {
        if($file['error']) {
            Alerts::add_error(l('global.error_message.file_upload') . ' (' . $file['error'] . ')');
            return null;
        }

        $extension = mb_strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if(!in_array($extension, $allowed_extensions)) {
            Alerts::add_error(l('global.error_message.invalid_file_type'));
            return null;
        }

        $max_size_mb = $max_size_mb ?? get_max_upload();

        if($file['size'] > $max_size_mb * 1000000) {
            Alerts::add_error(sprintf(l('global.error_message.file_size_limit'), $max_size_mb));
            return null;
        }

        $file_name = md5(time() . rand() . rand() . $file['name']) . '.' . $extension;
        move_uploaded_file($file['tmp_name'], UPLOADS_PATH . \Altum\Uploads::get_path('setup_projects') . $file_name);

        return $file_name;
    }
}
