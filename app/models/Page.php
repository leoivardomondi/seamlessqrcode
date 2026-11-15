<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\Models;

use Altum\Language;

defined('ALTUMCODE') || die();

class Page extends Model {

    public function get_pages($position) {

        $data = [];

        $cache_instance = cache()->getItem('pages_' . $position);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {
            $result = database()->query("SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon` FROM `pages` WHERE `position` = '{$position}' AND `is_published` = 1 ORDER BY `order`");

            while($row = $result->fetch_object()) {
                $data[] = $row;
            }

            cache()->save($cache_instance->set($data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('pages'));

        } else {

            /* Get cache */
            $data = $cache_instance->get();

        }

        foreach($data as $key => $value) {
            /* Make sure the language of the page still exists */
            if($value->language && !isset(\Altum\Language::$active_languages[$value->language])) {
                unset($data[$key]);
                continue;
            }



            if($value->type == 'internal') {
                $value->target = '_self';
                $value->url = SITE_URL . ($value->language ? \Altum\Language::$active_languages[$value->language] . '/' : null) . 'page/' . $value->url;
            } else {
                $value->target = $value->open_in_new_tab ? '_blank' : '_self';
            }



            /* Check language */
            if($value->language && $value->language != Language::$name) {
                unset($data[$key]);
            }
        }

        return $data;
    }

}
