<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

namespace Altum\Controllers;


defined('ALTUMCODE') || die();

class Logout extends Controller {

    public function index() {

        /* Exit admin impersonation */
        if(isset($_GET['admin_impersonate_user'])) {
            $admin_user_id = $_SESSION['admin_user_id'] ?? $_COOKIE['admin_impersonate_user_id'] ?? null;
            $admin_token_code = $_SESSION['admin_token_code'] ?? $_COOKIE['admin_impersonate_token_code'] ?? null;

            /* Logout of the current users */
            \Altum\Authentication::logout(false);

            $admin_user = $admin_user_id && $admin_token_code
                ? db()->where('user_id', $admin_user_id)->where('token_code', $admin_token_code)->where('type', 1, '>=')->getOne('users', ['user_id', 'password', 'token_code'])
                : null;

            if($admin_user) {
                /* Login as the admin */
                session_start();
                $_SESSION['user_id'] = $admin_user_id;
                $_SESSION['user_password_hash'] = md5($admin_user->password);

                setcookie('user_id', $admin_user->user_id, time()+60*60, COOKIE_PATH);
                setcookie('token_code', $admin_user->token_code, time()+60*60, COOKIE_PATH);
                setcookie('user_password_hash', md5($admin_user->password), time()+60*60, COOKIE_PATH);
            }

            setcookie('admin_impersonate_user_id', '', time()-30, COOKIE_PATH);
            setcookie('admin_impersonate_token_code', '', time()-30, COOKIE_PATH);

            redirect('admin/users');
        }

        /* Exit team delegated access */
        else if(isset($_GET['team'])) {
            unset($_SESSION['team_id']);
            redirect('teams-member');
        }

        /* Normal logout */
        else {
            \Altum\Authentication::logout();
        }

    }

}
