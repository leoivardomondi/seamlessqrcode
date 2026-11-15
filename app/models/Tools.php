<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\models;

defined('ALTUMCODE') || die();

class Tools extends Model {

    public function get_tools_usage() {

        $cache_instance = cache()->getItem('tools_usage');

        /* Set cache if not existing */
        if(!$cache_instance->get()) {

            $result = database()->query("SELECT * FROM `tools_usage` ORDER BY `total_views` DESC");
            $data = [];

            while($row = $result->fetch_object()) {
                $row->data = json_decode($row->data ?? '');
                $data[$row->tool_id] = $row;
            }

            cache()->save($cache_instance->set($data)->expiresAfter(3600));

        } else {

            /* Get cache */
            $data = $cache_instance->get('tools_usage');

        }

        return $data;
    }

}
