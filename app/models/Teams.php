<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class Teams extends Model {

    public function get_team_by_team_id($team_id) {

        /* Get the team */
        $team = null;

        /* Try to check if the resource exists via the cache */
        $cache_instance = cache()->getItem('team?team_id=' . $team_id);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            /* Get data from the database */
            $team = db()->where('team_id', $team_id)->getOne('teams');

            if($team) {
                cache()->save(
                    $cache_instance->set($team)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('user_id=' . $team->user_id)
                );
            }

        } else {

            /* Get cache */
            $team = $cache_instance->get();

        }

        return $team;

    }

}
