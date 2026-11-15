<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum;


defined('ALTUMCODE') || die();

class Affiliate {

    public static function initiate() {
        if(is_logged_in() || !\Altum\Plugin::is_active('affiliate') || (\Altum\Plugin::is_active('affiliate') && !settings()->affiliate->is_enabled)) {
            return;
        }

        $referral_key = isset($_GET['ref']) ? query_clean($_GET['ref']) : null;

        if(!$referral_key) {
            return;
        }

        /* Get the owner user of the referral key */
        if(!$user = db()->where('referral_key', $referral_key)->getOne('users', ['user_id', 'plan_settings', 'status', 'referral_key'])) {
            return;
        }

        /* Make sure the user is still active */
        if($user->status != 1) {
            return;
        }

        /* Make sure the user has access to the affiliate program */
        $user->plan_settings = json_decode($user->plan_settings);
        if(!$user->plan_settings->affiliate_commission_percentage) {
            return;
        }

        /* Set the tracking cookie */
        settings()->affiliate->tracking_type = settings()->affiliate->tracking_type ?? 'first';

        if(
            (settings()->affiliate->tracking_type == 'first' && !isset($_COOKIE['referred_by']))
            || settings()->affiliate->tracking_type == 'last'
        ) {
            setcookie('referred_by', $user->referral_key, time()+60*60*24*(settings()->affiliate->tracking_duration ?? 30), COOKIE_PATH);
        }

    }

}
