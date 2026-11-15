<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\models;

defined('ALTUMCODE') || die();

class BiolinksTemplates extends Model {

    public function get_biolinks_templates() {

        /* Get the user pixels */
        $biolinks_templates = [];

        /* Try to check if the user exists via the cache */
        $cache_instance = cache()->getItem('biolinks_templates');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $biolinks_templates_result = database()->query("SELECT * FROM `biolinks_templates` WHERE `is_enabled` = 1 ORDER BY `order`");
            while($row = $biolinks_templates_result->fetch_object()) {
                $row->settings = json_decode($row->settings ?? '');
                $biolinks_templates[$row->biolink_template_id] = $row;
            }

            cache()->save(
                $cache_instance->set($biolinks_templates)->expiresAfter(CACHE_DEFAULT_SECONDS)
            );

        } else {

            /* Get cache */
            $biolinks_templates = $cache_instance->get();

        }

        return $biolinks_templates;

    }

}
