<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Templates extends Model {

    public function get_templates() {
        /* Get the user projects */
        $templates = [];

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem('templates');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $templates_result = database()->query("SELECT * FROM `templates` WHERE `is_enabled` = 1 ORDER BY `order`");
            while($row = $templates_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $templates[$row->template_id] = $row;
            }

            cache()->save(
                $cache_instance->set($templates)->expiresAfter(CACHE_DEFAULT_SECONDS)
            );

        } else {

            /* Get cache */
            $templates = $cache_instance->get();

        }

        return $templates;

    }

}
