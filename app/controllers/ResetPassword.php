<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Logger;
use Altum\Models\User;

defined('ALTUMCODE') || die();

class ResetPassword extends Controller {

    public function index() {

        \Altum\Authentication::guard('guest');

        $md5email = (isset($this->params[0])) ? $this->params[0] : null;
        $lost_password_code = (isset($this->params[1])) ? $this->params[1] : null;
        $redirect = process_and_get_redirect_params() ?? 'dashboard';
        $welcome = isset($_GET['welcome']) ? (strpos($redirect, '?') !== false ? '&' : '?') . 'welcome=' . $_GET['welcome'] : null;

        if(!$md5email || !$lost_password_code || mb_strlen($lost_password_code) < 1) redirect();

        /* Check if the lost password code is correct */
        $user = db()->where('lost_password_code', $lost_password_code)->getOne('users', ['user_id', 'email', 'name', 'password']);

        if(!$user) {
            redirect();
        }

        if(md5($user->email) != $md5email) {
            redirect();
        }

        if(!empty($_POST)) {

            //ALTUMCODE:DEMO if(DEMO) Alerts::add_error('This command is blocked on the demo.');

            /* Check for any errors */
            if(mb_strlen($_POST['new_password']) < 6 || mb_strlen($_POST['new_password']) > 64) {
                Alerts::add_field_error('new_password', l('global.error_message.password_length'));
            }
            if($_POST['new_password'] !== $_POST['repeat_password']) {
                Alerts::add_field_error('repeat_password', l('global.error_message.passwords_not_matching'));
            }

            if(!Alerts::has_field_errors() && !Alerts::has_errors()) {
                /* Encrypt the new password */
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

                /* Update the password & empty the reset code from the database */
                db()->where('user_id', $user->user_id)->update('users', [
                    'password' => $new_password,
                    'twofa_secret' => null,
                    'lost_password_code' => null
                ]);

                Logger::users($user->user_id, 'reset_password.success');

                /* Set a nice success message */
                Alerts::add_success(l('reset_password.success_message'));

                /* Log the user in and persist */
                $cookie_days = max(365, (int) (settings()->users->login_rememberme_cookie_days ?? 365));
                $token_code = $user->token_code ?? '';
                if(empty($token_code)) {
                    $token_code = md5($user->email . microtime());
                    db()->where('user_id', $user->user_id)->update('users', ['token_code' => $token_code]);
                }
                setcookie('user_id', $user->user_id, time() + 60*60*24 * $cookie_days, COOKIE_PATH);
                setcookie('token_code', $token_code, time() + 60*60*24 * $cookie_days, COOKIE_PATH);
                setcookie('user_password_hash', md5($new_password), time() + 60*60*24 * $cookie_days, COOKIE_PATH);

                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['user_password_hash'] = md5($new_password);

                (new User())->login_aftermath_update($user->user_id);

                /* Clear the cache */
                cache()->deleteItemsByTag('user_id=' . $user->user_id);

                redirect($redirect);
            }
        }

        /* Prepare the view */
        $data = [];

        $view = new \Altum\View('reset-password/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}
